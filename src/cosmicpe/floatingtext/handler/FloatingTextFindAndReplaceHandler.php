<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\handler;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;

final class FloatingTextFindAndReplaceHandler implements FloatingTextHandler{

	public function __construct(
		private string $find,
		private string $replace
	){}

	public function canHandle(FloatingText $text) : bool{
		return strpos($text->getLine(), $this->find) !== false;
	}

	public function onSpawn(FloatingText $text, FloatingTextEntity $entity) : void{
		$entity->setNameTag(str_replace($this->find, $this->replace, $entity->getNameTag()));
	}

	public function onDespawn(FloatingText $text, FloatingTextEntity $entity) : void{
		// NOOP
	}
}