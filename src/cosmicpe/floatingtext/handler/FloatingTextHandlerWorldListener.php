<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\handler;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;
use cosmicpe\floatingtext\world\WorldInstance;
use cosmicpe\floatingtext\world\WorldListener;

final class FloatingTextHandlerWorldListener implements WorldListener{

	public function onWorldFloatingTextSpawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		FloatingTextHandlerManager::onWorldFloatingTextSpawn($id, $text, $entity);
	}

	public function onWorldFloatingTextDespawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		FloatingTextHandlerManager::onWorldFloatingTextDespawn( $id, $text, $entity);
	}

	public function onWorldFloatingTextAdd(WorldInstance $world, int $id, FloatingText $text) : void{
		FloatingTextHandlerManager::onWorldFloatingTextAdd($id, $text);
	}

	public function onWorldFloatingTextUpdate(WorldInstance $world, int $id, FloatingText $text) : void{
		FloatingTextHandlerManager::onWorldFloatingTextUpdate($id, $text);
	}

	public function onWorldFloatingTextRemove(WorldInstance $world, int $id) : void{
		FloatingTextHandlerManager::onWorldFloatingTextRemove($id);
	}

	public function onWorldAdd(WorldInstance $world) : void{
		// NOOP
	}

	public function onWorldRemove(WorldInstance $world) : void{
		// NOOP
	}
}