<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext;

use cosmicpe\floatingtext\db\Database;
use cosmicpe\floatingtext\handler\FloatingTextHandlerManager;
use cosmicpe\floatingtext\world\WorldManager;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\PluginBase;
use RuntimeException;

final class Loader extends PluginBase{

	private Database $database;
	private WorldManager $world_manager;
	private FloatingTextHandlerManager $handler_manager;

	protected function onLoad() : void{
		$this->world_manager = new WorldManager();
		$this->handler_manager = new FloatingTextHandlerManager($this->world_manager);
	}

	protected function onEnable() : void{
		$this->database = new Database($this);
		$this->world_manager->init($this);

		$command = $this->getCommand("floatingtext");
		if(!($command instanceof PluginCommand)){
			throw new RuntimeException("Cannot find command \"floatingtext\"");
		}
		$command->setExecutor(new FloatingTextCommandExecutor($this->database, $this->world_manager));
	}

	protected function onDisable() : void{
		$this->database->close();
	}

	public function getDatabase() : Database{
		return $this->database;
	}

	public function getWorldManager() : WorldManager{
		return $this->world_manager;
	}

	public function getHandlerManager() : FloatingTextHandlerManager{
		return $this->handler_manager;
	}
}