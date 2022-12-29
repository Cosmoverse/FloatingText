<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext;

use Closure;
use cosmicpe\floatingtext\db\Database;
use cosmicpe\floatingtext\world\WorldInstance;
use cosmicpe\floatingtext\world\WorldManager;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use function array_map;
use function array_pop;
use function array_shift;
use function array_slice;
use function count;
use function explode;
use function implode;
use function sprintf;

final class FloatingTextCommandExecutor implements CommandExecutor{

	public function __construct(
		private Database $database,
		private WorldManager $world_manager
	){}

	/**
	 * @param Position $pos
	 * @param string $line
	 * @param Closure(int, FloatingText) : void $callback
	 */
	private function addFloatingText(Position $pos, string $line, Closure $callback) : void{
		$text = new FloatingText($pos->getWorld()->getFolderName(), $pos->x, $pos->y, $pos->z, $line);
		$this->database->add($text, function(int $id) use($pos, $text, $callback) : void{
			$this->world_manager->get($pos->getWorld())->add($id, $text);
			$callback($id, $text);
		});
	}

	private function parseInt(string $argument, string $name) : int{
		$id = (int) $argument;
		if($argument !== (string) $id){
			throw new CommandException("Invalid {$name}: {$id}");
		}
		return $id;
	}

	private function parseFloatingTextId(string $argument) : int{
		return $this->parseInt($argument, "floating text id");
	}

	private function getWorldForTextModification(World $world) : WorldInstance{
		$instance = $this->world_manager->get($world);
		if($instance->isLoading()){
			throw new CommandException("Cannot modify text while the world is loading. Try again after some time.");
		}
		return $instance;
	}

	private function getTextInWorld(WorldInstance $world, int $id) : FloatingText{
		return $world->getText($id) ?? throw new CommandException("No floating text with the ID {$id} was found!");
	}

	/**
	 * @param CommandSender $sender
	 * @param Command $command
	 * @param string $label
	 * @param string[] $args
	 */
	private function executeCommand(CommandSender $sender, Command $command, string $label, array $args) : void{
		if(!($sender instanceof Player)){
			throw new CommandException("This command must be used as a player.");
		}

		if(isset($args[0])){
			switch($args[0]){
				case "add":
					if(!isset($args[1])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <...text>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: You may use & for colour codes."
						);
					}

					$line = TextFormat::colorize(implode(" ", array_slice($args, 1)));
					$this->addFloatingText($sender->getPosition(), $line, static function(int $id, FloatingText $text) use($sender) : void{
						if(!($sender instanceof Player) || $sender->isOnline()){
							$sender->sendMessage(TextFormat::GREEN . "Added floating text at your position!");
							$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $text->x, $text->y, $text->z, $text->world));
							$sender->sendMessage(TextFormat::GREEN . "Text: {$text->line}");
						}
					});
					return;
				case "prepend":
					if(!isset($args[1]) || !isset($args[2])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <id> <...line>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/{$label} near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}

					$id = $this->parseFloatingTextId($args[1]);
					$world = $this->getWorldForTextModification($sender->getWorld());
					$text = $this->getTextInWorld($world, $id);

					$line = TextFormat::colorize(implode(" ", array_slice($args, 2)));
					$text = new FloatingText($text->world, $text->x, $text->y, $text->z, $line . TextFormat::EOL . $text->line);
					$world->update($id, $text);

					$sender->sendMessage(TextFormat::GREEN . "Prepended floating text #{$id}!");
					$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $text->x, $text->y, $text->z, $text->world));
					$sender->sendMessage(TextFormat::GREEN . "Prepended Text: {$line}");
					return;
				case "append":
					if(!isset($args[1]) || !isset($args[2])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <id> <...line>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/{$label} near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}

					$id = $this->parseFloatingTextId($args[1]);
					$world = $this->getWorldForTextModification($sender->getWorld());
					$text = $this->getTextInWorld($world, $id);

					$line = TextFormat::colorize(implode(" ", array_slice($args, 2)));
					$text = new FloatingText($text->world, $text->x, $text->y, $text->z, $text->line . TextFormat::EOL . $line);
					$world->update($id, $text);

					$sender->sendMessage(TextFormat::GREEN . "Appended floating text #{$id}!");
					$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $text->x, $text->y, $text->z, $text->world));
					$sender->sendMessage(TextFormat::GREEN . "Appended Text: {$line}");
					return;
				case "shift":
					if(!isset($args[1])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <id>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/{$label} near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}

					$id = $this->parseFloatingTextId($args[1]);
					$world = $this->getWorldForTextModification($sender->getWorld());
					$text = $this->getTextInWorld($world, $id);

					$line = explode(TextFormat::EOL, $text->line);
					$shifted = array_shift($line);
					$text = new FloatingText($text->world, $text->x, $text->y, $text->z, implode(TextFormat::EOL, $line));
					$world->update($id, $text);

					$sender->sendMessage(TextFormat::GREEN . "Shifted floating text #{$id}!");
					$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $text->x, $text->y, $text->z, $text->world));
					$sender->sendMessage(TextFormat::GREEN . "Shifted Text: {$shifted}");
					return;
				case "pop":
					if(!isset($args[1])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <id>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/{$label} near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}

					$id = $this->parseFloatingTextId($args[1]);
					$world = $this->getWorldForTextModification($sender->getWorld());
					$text = $this->getTextInWorld($world, $id);

					$line = explode(TextFormat::EOL, $text->line);
					$pop = array_pop($line);
					$text = new FloatingText($text->world, $text->x, $text->y, $text->z, implode(TextFormat::EOL, $line));
					$world->update($id, $text);

					$sender->sendMessage(TextFormat::GREEN . "Popped floating text #{$id}!");
					$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $text->x, $text->y, $text->z, $text->world));
					$sender->sendMessage(TextFormat::GREEN . "Popped Text: {$pop}");
					return;
				case "split":
					if(!isset($args[1])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <id>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/{$label} near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}

					$id = $this->parseFloatingTextId($args[1]);
					$world = $this->getWorldForTextModification($sender->getWorld());
					$text = $this->getTextInWorld($world, $id);

					$step = -0.275;

					$lines = explode(TextFormat::EOL, $text->line);
					if(count($lines) === 1){
						throw new CommandException("Floating text #{$id} contains only one line!");
					}

					$text = new FloatingText($text->world, $text->x, $text->y - ($step * count($lines) * 0.5), $text->z, array_shift($lines));
					$world->update($id, $text);
					$offset = $step;
					foreach($lines as $line){
						$this->addFloatingText(new Position($text->x, $text->y + $offset, $text->z, $sender->getWorld()), $line, static function(int $id, FloatingText $text) : void{});
						$offset += $step;
					}

					$sender->sendMessage(TextFormat::GREEN . "Split floating text #{$id}!");
					$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $text->x, $text->y, $text->z, $text->world));
					$sender->sendMessage(TextFormat::GREEN . "Number of splits: " . (count($lines) + 1));
					return;
				case "combine":
					if(!isset($args[1]) || !isset($args[2])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <...ids>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/{$label} near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}

					$world = $this->getWorldForTextModification($sender->getWorld());
					$texts = [];
					foreach(array_slice($args, 1) as $id_arg){
						$id = $this->parseFloatingTextId($id_arg);
						$text = $this->getTextInWorld($world, $id);
						$texts[] = $text;
					}

					$line = implode(TextFormat::EOL, array_map(static function(FloatingText $text) : string{ return $text->line; }, $texts));
					$this->addFloatingText($sender->getPosition(), $line, static function(int $id, FloatingText $text) use($sender) : void{
						if(!($sender instanceof Player) || $sender->isOnline()){
							$sender->sendMessage(TextFormat::GREEN . "Added floating text at your position!");
							$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $text->x, $text->y, $text->z, $text->world));
							$sender->sendMessage(TextFormat::GREEN . "Text: {$text->line}");
						}
					});
					return;
				case "set":
					if(!isset($args[1]) || !isset($args[2]) || !isset($args[3])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <id> <line_number> <...text>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/{$label} near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}

					$id = $this->parseFloatingTextId($args[1]);
					$line_number = $this->parseInt($args[2], "line number");
					$world = $this->getWorldForTextModification($sender->getWorld());
					$text = $this->getTextInWorld($world, $id);

					$lines = explode(TextFormat::EOL, $text->line);
					if(!isset($lines[$line_number - 1])){
						throw new CommandException("Line #{$line_number} does not exist floating text with the ID {$id}!");
					}

					$lines[$line_number - 1] = $new_text = TextFormat::colorize(implode(" ", array_slice($args, 3)));
					$text = new FloatingText($text->world, $text->x, $text->y, $text->z, implode(TextFormat::EOL, $lines));
					$world->update($id, $text);

					$sender->sendMessage(TextFormat::GREEN . "Updated floating text #{$id}'s line #{$line_number}!");
					$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $text->x, $text->y, $text->z, $text->world));
					$sender->sendMessage(TextFormat::GREEN . "Updated line: {$line_number}");
					$sender->sendMessage(TextFormat::GREEN . "New Text: {$new_text}");
					return;
				case "move":
					if(!isset($args[1])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <id>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/{$label} near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}

					$id = $this->parseFloatingTextId($args[1]);
					$world = $this->getWorldForTextModification($sender->getWorld());
					$old_text = $this->getTextInWorld($world, $id);

					$new_pos = $sender->getPosition();
					$new_text = new FloatingText($old_text->world, $new_pos->x, $new_pos->y, $new_pos->z, $old_text->line);
					$world->update($id, $new_text);

					$sender->sendMessage(TextFormat::GREEN . "Moved floating text #{$id}!");
					$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $old_text->x, $old_text->y, $old_text->z, $old_text->world));
					$sender->sendMessage(TextFormat::GREEN . sprintf("New Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $new_text->x, $new_text->y, $new_text->z, $new_text->world));
					return;
				case "near":
					$world = $sender->getWorld();
					$found = 0;
					foreach($world->getNearbyEntities($sender->getBoundingBox()->expandedCopy(8, 8, 8)) as $entity){
						if($entity instanceof FloatingTextEntity){
							$sender->sendMessage(TextFormat::GRAY . "#{$entity->getFloatingTextId()}: " . TextFormat::RESET . $entity->getNameTag());
							++$found;
						}
					}
					$sender->sendMessage($found > 0 ? TextFormat::GRAY . "Found " . TextFormat::WHITE . $found . TextFormat::GRAY . " floating texts near you!" : TextFormat::RED . "No floating texts were found nearby!");
					return;
				case "remove":
					if(!isset($args[1])){
						throw new CommandException(
							"Usage: /{$label} {$args[0]} <id>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/{$label} near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}

					$id = $this->parseFloatingTextId($args[1]);
					$world = $this->getWorldForTextModification($sender->getWorld());
					try{
						$text = $world->remove($id);
					}catch(InvalidArgumentException){
						throw new CommandException("No floating text with the ID {$id} was found!");
					}

					$sender->sendMessage(TextFormat::GREEN . "Removed floating text #{$id}!");
					$sender->sendMessage(TextFormat::GREEN . sprintf("Position: x=%.4f, y=%.4f, z=%.4f, world=%s", $text->x, $text->y, $text->z, $text->world));
					$sender->sendMessage(TextFormat::GREEN . "Text: {$text->line}");
					return;
			}
		}

		throw new CommandException(
			TextFormat::BOLD . TextFormat::BLUE . "Floating Text Command" . TextFormat::RESET . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} add <...text>" . TextFormat::GRAY . " - Adds a floating text at your location" . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} prepend <id> <...text>" . TextFormat::GRAY . " - Prepends a line to a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} append <id> <...text>" . TextFormat::GRAY . " - Appends a line to a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} shift <id>" . TextFormat::GRAY . " - Shifts a line off of a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} pop <id>" . TextFormat::GRAY . " - Pops a line off of a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} split <id>" . TextFormat::GRAY . " - Separate a multi-line floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} set <id> <...text>" . TextFormat::GRAY . " - Changes an existing line's value on a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} move <id>" . TextFormat::GRAY . " - Moves a floating text to your location" . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} near" . TextFormat::GRAY . " - Lists all floating texts near your location" . TextFormat::EOL .
			TextFormat::BLUE . "/{$label} remove <id>" . TextFormat::GRAY . " - Removes a floating text"
		);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		try{
			$this->executeCommand($sender, $command, $label, $args);
		}catch(CommandException $e){
			$sender->sendMessage(TextFormat::RED . $e->getMessage());
			return false;
		}
		return true;
	}
}