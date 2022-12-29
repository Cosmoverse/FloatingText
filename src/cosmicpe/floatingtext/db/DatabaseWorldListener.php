<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\db;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;
use cosmicpe\floatingtext\world\WorldInstance;
use cosmicpe\floatingtext\world\WorldListener;

final class DatabaseWorldListener implements WorldListener{

	public function __construct(
		private Database $database,
	){}

	public function onWorldAdd(WorldInstance $world) : void{
		$this->database->load($world->getWorld()->getFolderName(), static function(array $texts) use($world) : void{
			if($world->getWorld()->isLoaded()){
				$world->load($texts);
			}
		});
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