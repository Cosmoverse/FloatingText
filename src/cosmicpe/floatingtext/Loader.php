<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext;

use cosmicpe\floatingtext\db\Database;
use cosmicpe\floatingtext\handler\FloatingTextHandlerManager;
use cosmicpe\floatingtext\world\WorldManager;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\EntityFactory;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

final class Loader extends PluginBase{

	/** @var Database */
	private $database;

	protected function onEnable() : void{
		EntityFactory::getInstance()->register(FloatingTextEntity::class, ["cosmicpe:floating_text"]);
		$this->database = new Database($this);
		FloatingTextHandlerManager::init();
		WorldManager::init($this);
	}

	public function getDatabase() : Database{
		return $this->database;
	}

	protected function onDisable() : void{
		$this->database->close();
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(!($sender instanceof Player)){
			$sender->sendMessage(TextFormat::RED . "This command must be used as a player.");
			return false;
		}

		if(isset($args[0])){
			switch($args[0]){
				case "add":
					if(isset($args[1])){
						$line = TextFormat::colorize(implode(" ", array_slice($args, 1)));
						$world = $sender->getWorld();
						$pos = $sender->getPosition();
						$this->database->add($text = new FloatingText($world->getFolderName(), $pos->x, $pos->y, $pos->z, $line), static function(int $id) use($sender, $world, $text) : void{
							WorldManager::get($world)->add($id, $text);
							$sender->sendMessage(TextFormat::GREEN . "Added floating text at your position!");
							$sender->sendMessage(TextFormat::GREEN . "Position: x=" . sprintf("%0.4f", $text->getX()) . ", y=" . sprintf("%0.4f", $text->getY()) . ", z=" . sprintf("%0.4f", $text->getZ()) . " world=" . $text->getWorld());
							$sender->sendMessage(TextFormat::GREEN . "Text: " . $text->getLine());
						});
					}else{
						$sender->sendMessage(
							TextFormat::RED . "Usage: /" . $label . " " . $args[0] . " <...text>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: You may use & for colour codes."
						);
					}
					return true;
				case "prepend":
					if(isset($args[1]) && isset($args[2])){
						$id = (int) $args[1];
						if($args[1] === "$id"){
							$world = WorldManager::get($sender->getWorld());
							$text = $world->getText($id);
							if($text === null){
								$sender->sendMessage(TextFormat::RED . "No floating text with the ID " . $id . " was found!");
								return false;
							}

							$line = TextFormat::colorize(implode(" ", array_slice($args, 2)));
							$text->setLine($line . TextFormat::EOL . $text->getLine());
							$world->update($id, $text);

							$sender->sendMessage(TextFormat::GREEN . "Prepended floating text #" . $id . "!");
							$sender->sendMessage(TextFormat::GREEN . "Position: x=" . sprintf("%0.4f", $text->getX()) . ", y=" . sprintf("%0.4f", $text->getY()) . ", z=" . sprintf("%0.4f", $text->getZ()) . " world=" . $text->getWorld());
							$sender->sendMessage(TextFormat::GREEN . "Prepended Text: " . $line);
						}else{
							$sender->sendMessage(TextFormat::RED . "Invalid floating text id: " . $id);
						}
					}else{
						$sender->sendMessage(
							TextFormat::RED . "Usage: /" . $label . " " . $args[0] . " <id> <...line>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/" . $label . " near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}
					return true;
				case "append":
					if(isset($args[1]) && isset($args[2])){
						$id = (int) $args[1];
						if($args[1] === "$id"){
							$world = WorldManager::get($sender->getWorld());
							$text = $world->getText($id);
							if($text === null){
								$sender->sendMessage(TextFormat::RED . "No floating text with the ID " . $id . " was found!");
								return false;
							}

							$line = TextFormat::colorize(implode(" ", array_slice($args, 2)));
							$text->setLine($text->getLine() . TextFormat::EOL . $line);
							$world->update($id, $text);

							$sender->sendMessage(TextFormat::GREEN . "Appended floating text #" . $id . "!");
							$sender->sendMessage(TextFormat::GREEN . "Position: x=" . sprintf("%0.4f", $text->getX()) . ", y=" . sprintf("%0.4f", $text->getY()) . ", z=" . sprintf("%0.4f", $text->getZ()) . " world=" . $text->getWorld());
							$sender->sendMessage(TextFormat::GREEN . "Appended Text: " . $line);
						}else{
							$sender->sendMessage(TextFormat::RED . "Invalid floating text id: " . $id);
						}
					}else{
						$sender->sendMessage(
							TextFormat::RED . "Usage: /" . $label . " " . $args[0] . " <id> <...line>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/" . $label . " near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}
					return true;
				case "shift":
					if(isset($args[1])){
						$id = (int) $args[1];
						if($args[1] === "$id"){
							$world = WorldManager::get($sender->getWorld());
							$text = $world->getText($id);
							if($text === null){
								$sender->sendMessage(TextFormat::RED . "No floating text with the ID " . $id . " was found!");
								return false;
							}

							$line = explode(TextFormat::EOL, $text->getLine());
							$shifted = array_shift($line);
							$text->setLine(implode(TextFormat::EOL, $line));
							$world->update($id, $text);

							$sender->sendMessage(TextFormat::GREEN . "Shifted floating text #" . $id . "!");
							$sender->sendMessage(TextFormat::GREEN . "Position: x=" . sprintf("%0.4f", $text->getX()) . ", y=" . sprintf("%0.4f", $text->getY()) . ", z=" . sprintf("%0.4f", $text->getZ()) . " world=" . $text->getWorld());
							$sender->sendMessage(TextFormat::GREEN . "Shifted Text: " . $shifted);
						}else{
							$sender->sendMessage(TextFormat::RED . "Invalid floating text id: " . $id);
						}
					}else{
						$sender->sendMessage(
							TextFormat::RED . "Usage: /" . $label . " " . $args[0] . " <id>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/" . $label . " near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}
					return true;
				case "pop":
					if(isset($args[1])){
						$id = (int) $args[1];
						if($args[1] === "$id"){
							$world = WorldManager::get($sender->getWorld());
							$text = $world->getText($id);
							if($text === null){
								$sender->sendMessage(TextFormat::RED . "No floating text with the ID " . $id . " was found!");
								return false;
							}

							$line = explode(TextFormat::EOL, $text->getLine());
							$pop = array_pop($line);
							$text->setLine(implode(TextFormat::EOL, $line));
							$world->update($id, $text);

							$sender->sendMessage(TextFormat::GREEN . "Popped floating text #" . $id . "!");
							$sender->sendMessage(TextFormat::GREEN . "Position: x=" . sprintf("%0.4f", $text->getX()) . ", y=" . sprintf("%0.4f", $text->getY()) . ", z=" . sprintf("%0.4f", $text->getZ()) . " world=" . $text->getWorld());
							$sender->sendMessage(TextFormat::GREEN . "Popped Text: " . $pop);
						}else{
							$sender->sendMessage(TextFormat::RED . "Invalid floating text id: " . $id);
						}
					}else{
						$sender->sendMessage(
							TextFormat::RED . "Usage: /" . $label . " " . $args[0] . " <id>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/" . $label . " near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}
					return true;
				case "set":
					if(isset($args[1]) && isset($args[2]) && isset($args[3])){
						$id = (int) $args[1];
						$line_number = (int) $args[2];
						if($args[1] === "$id"){
							if($args[2] === "$line_number"){
								$world = WorldManager::get($sender->getWorld());
								$text = $world->getText($id);
								if($text === null){
									$sender->sendMessage(TextFormat::RED . "No floating text with the ID " . $id . " was found!");
									return false;
								}

								$lines = explode(TextFormat::EOL, $text->getLine());
								if(!isset($lines[$line_number - 1])){
									$sender->sendMessage(TextFormat::RED . "Line #" . $line_number . " does not exist floating text with the ID " . $id . "!");
									return false;
								}

								$lines[$line_number - 1] = $new_text = TextFormat::colorize(implode(" ", array_slice($args, 3)));
								$text->setLine(implode(TextFormat::EOL, $lines));
								$world->update($id, $text);

								$sender->sendMessage(TextFormat::GREEN . "Updated floating text #" . $id . "'s line #" . $line_number . "!");
								$sender->sendMessage(TextFormat::GREEN . "Position: x=" . sprintf("%0.4f", $text->getX()) . ", y=" . sprintf("%0.4f", $text->getY()) . ", z=" . sprintf("%0.4f", $text->getZ()) . " world=" . $text->getWorld());
								$sender->sendMessage(TextFormat::GREEN . "Updated line: " . $line_number);
								$sender->sendMessage(TextFormat::GREEN . "New Text: " . $new_text);
							}else{
								$sender->sendMessage(TextFormat::RED . "Invalid line number: " . $line_number);
							}
						}else{
							$sender->sendMessage(TextFormat::RED . "Invalid floating text id: " . $id);
						}
					}else{
						$sender->sendMessage(
							TextFormat::RED . "Usage: /" . $label . " " . $args[0] . " <id> <line_number> <...text>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/" . $label . " near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}
					return true;
				case "move":
					if(isset($args[1])){
						$id = (int) $args[1];
						if($args[1] === "$id"){
							$world = WorldManager::get($sender->getWorld());
							$text = $world->getText($id);
							if($text === null){
								$sender->sendMessage(TextFormat::RED . "No floating text with the ID " . $id . " was found!");
								return false;
							}

							$old_pos = new Vector3($text->getX(), $text->getY(), $text->getZ());
							$new_pos = $sender->getPosition();
							$new_text = clone $text;
							$new_text->setPosition($new_pos->x, $new_pos->y, $new_pos->z);
							$world->update($id, $new_text);

							$sender->sendMessage(TextFormat::GREEN . "Popped floating text #" . $id . "!");
							$sender->sendMessage(TextFormat::GREEN . "Position: x=" . sprintf("%0.4f", $old_pos->x) . ", y=" . sprintf("%0.4f", $old_pos->y) . ", z=" . sprintf("%0.4f", $old_pos->z) . " world=" . $text->getWorld());
							$sender->sendMessage(TextFormat::GREEN . "New Position: x=" . sprintf("%0.4f", $new_pos->x) . ", y=" . sprintf("%0.4f", $new_pos->y) . ", z=" . sprintf("%0.4f", $new_pos->z) . " world=" . $text->getWorld());
						}else{
							$sender->sendMessage(TextFormat::RED . "Invalid floating text id: " . $id);
						}
					}else{
						$sender->sendMessage(
							TextFormat::RED . "Usage: /" . $label . " " . $args[0] . " <id>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/" . $label . " near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}
					return true;
				case "near":
					$world = $sender->getWorld();
					$found = 0;
					foreach($world->getNearbyEntities($sender->getBoundingBox()->expandedCopy(8, 8, 8)) as $entity){
						if($entity instanceof FloatingTextEntity){
							$sender->sendMessage(TextFormat::GRAY . "#" . $entity->getFloatingTextId() . ": " . TextFormat::RESET . $entity->getNameTag());
							++$found;
						}
					}
					$sender->sendMessage($found > 0 ? TextFormat::GRAY . "Found " . TextFormat::WHITE . $found . TextFormat::GRAY . " floating texts near you!" : TextFormat::RED . "No floating texts were found nearby!");
					return true;
				case "remove":
					if(isset($args[1])){
						$id = (int) $args[1];
						if($args[1] === "$id"){
							try{
								$text = WorldManager::get($sender->getWorld())->remove($id);
							}catch(InvalidArgumentException $e){
								$sender->sendMessage(TextFormat::RED . "No floating text with the ID " . $id . " was found!");
								return false;
							}
							$sender->sendMessage(TextFormat::GREEN . "Removed floating text #" . $id . "!");
							$sender->sendMessage(TextFormat::GREEN . "Position: x=" . sprintf("%0.4f", $text->getX()) . ", y=" . sprintf("%0.4f", $text->getY()) . ", z=" . sprintf("%0.4f", $text->getZ()) . " world=" . $text->getWorld());
							$sender->sendMessage(TextFormat::GREEN . "Text: " . $text->getLine());
						}else{
							$sender->sendMessage(TextFormat::RED . "Invalid floating text id: " . $id);
						}
					}else{
						$sender->sendMessage(
							TextFormat::RED . "Usage: /" . $label . " " . $args[0] . " <id>" . TextFormat::EOL .
							TextFormat::GRAY . "Hint: Use " . TextFormat::RED . "/" . $label . " near" . TextFormat::GRAY . " to list nearby floating texts along with their <id>."
						);
					}
					return true;
			}
		}

		$sender->sendMessage(
			TextFormat::BOLD . TextFormat::BLUE . "Floating Text Command" . TextFormat::RESET . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " add <...text>" . TextFormat::GRAY . " - Adds a floating text at your location" . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " prepend <id> <...text>" . TextFormat::GRAY . " - Prepends a line to a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " append <id> <...text>" . TextFormat::GRAY . " - Appends a line to a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " shift <id>" . TextFormat::GRAY . " - Shifts a line off of a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " pop <id>" . TextFormat::GRAY . " - Pops a line off of a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " set <id> <...text>" . TextFormat::GRAY . " - Changes an existing line's value on a floating text" . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " move <id>" . TextFormat::GRAY . " - Moves a floating text to your location" . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " near" . TextFormat::GRAY . " - Lists all floating texts near your location" . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " remove <id>" . TextFormat::GRAY . " - Removes a floating text"
		);
		return false;
	}
}