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
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

final class Loader extends PluginBase{

	/** @var Database */
	private $database;

	protected function onEnable() : void{
		EntityFactory::register(FloatingTextEntity::class, ["cosmicpe:floating_text"]);
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
			TextFormat::BLUE . "/" . $label . " near" . TextFormat::GRAY . " - Lists all floating texts near your location" . TextFormat::EOL .
			TextFormat::BLUE . "/" . $label . " remove <id>" . TextFormat::GRAY . " - Removes a floating text"
		);
		return false;
	}
}