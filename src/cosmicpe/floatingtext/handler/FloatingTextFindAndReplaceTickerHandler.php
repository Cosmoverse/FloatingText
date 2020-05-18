<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\handler;

use Closure;
use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;

final class FloatingTextFindAndReplaceTickerHandler implements FloatingTextHandler{

	/** @var string */
	private $find;

	/** @var Closure */
	private $replace;

	/** @var FloatingTextEntity[] */
	private $entities = [];

	public function __construct(Plugin $plugin, string $find, Closure $replace, int $interval = 20){
		$this->find = $find;
		$this->replace = $replace;
		$plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			foreach($this->entities as $entity){
				$this->updateEntity($entity);
			}
		}), $interval);
	}

	public function canHandle(FloatingText $text) : bool{
		return strpos($text->getLine(), $this->find) !== false;
	}

	public function updateEntity(FloatingTextEntity $entity) : void{
		$entity->setNameTag(str_replace($this->find, ($this->replace)(), $entity->getFloatingText()->getLine()));
	}

	public function onSpawn(FloatingText $text, FloatingTextEntity $entity) : void{
		$this->entities[$entity->getId()] = $entity;
		$this->updateEntity($entity);
	}

	public function onDespawn(FloatingText $text, FloatingTextEntity $entity) : void{
		unset($this->entities[$entity->getId()]);
	}
}