<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\db;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;
use cosmicpe\floatingtext\world\WorldInstance;
use cosmicpe\floatingtext\world\WorldListener;

final class DatabaseWorldListener implements WorldListener{

	/** @var Database */
	private $database;

	/** @var bool */
	private $wait_until_load;

	public function __construct(Database $database, bool $wait_until_load = false){
		$this->database = $database;
		$this->wait_until_load = $wait_until_load;
	}

	public function onWorldAdd(WorldInstance $world) : void{
		$this->database->load($world->getWorld()->getFolderName(), static function(array $texts) use($world) : void{ $world->load($texts); });
		if($this->wait_until_load){
			$this->database->waitAll();
		}
	}

	public function onWorldFloatingTextUpdate(WorldInstance $world, int $id, FloatingText $text) : void{
		$this->database->update($id, $text);
	}

	public function onWorldFloatingTextRemove(WorldInstance $world, int $id) : void{
		$this->database->remove($id);
	}

	public function onWorldFloatingTextAdd(WorldInstance $world, int $id, FloatingText $text) : void{
		// NOOP
	}

	public function onWorldFloatingTextSpawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		// NOOP
	}

	public function onWorldFloatingTextDespawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		// NOOP
	}

	public function onWorldRemove(WorldInstance $world) : void{
		// NOOP
	}
}