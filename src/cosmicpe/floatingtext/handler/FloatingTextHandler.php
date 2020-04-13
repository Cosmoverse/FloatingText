<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\handler;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;

interface FloatingTextHandler{

	public function canHandle(FloatingText $text) : bool;

	public function onSpawn(FloatingText $text, FloatingTextEntity $entity) : void;

	public function onDespawn(FloatingText $text, FloatingTextEntity $entity) : void;
}