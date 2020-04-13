<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\world;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;

interface WorldListener{

	public function onWorldAdd(WorldInstance $world) : void;

	public function onWorldFloatingTextAdd(WorldInstance $world, int $id, FloatingText $text) : void;

	public function onWorldFloatingTextUpdate(WorldInstance $world, int $id, FloatingText $text) : void;

	public function onWorldFloatingTextRemove(WorldInstance $world, int $id) : void;

	public function onWorldFloatingTextSpawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void;

	public function onWorldFloatingTextDespawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void;

	public function onWorldRemove(WorldInstance $world) : void;
}