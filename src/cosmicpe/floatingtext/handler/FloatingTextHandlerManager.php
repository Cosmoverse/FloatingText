<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\handler;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;
use cosmicpe\floatingtext\world\WorldInstance;
use cosmicpe\floatingtext\world\WorldListener;
use cosmicpe\floatingtext\world\WorldManager;

final class FloatingTextHandlerManager implements WorldListener{

	/** @var array<int, FloatingTextHandler> */
	private array $handlers = [];

	/** @var array<int, array<int, int>> */
	private array $id_handlers = [];

	public function __construct(
		private WorldManager $world_manager
	){
		$this->world_manager->addListener($this);
	}

	public function register(FloatingTextHandler $handler) : void{
		$this->handlers[$handler_id = spl_object_id($handler)] = $handler;
		foreach($this->world_manager->getAll() as $world){
			foreach($world->getAllFloatingTexts() as $id => $text){
				if($handler->canHandle($text)){
					$this->id_handlers[$id][$handler_id] = $handler_id;
					$entity = $world->getTextEntity($id);
					if($entity !== null){
						$handler->onSpawn($text, $entity);
					}
				}
			}
		}
	}

	/**
	 * @internal
	 */
	public function onWorldFloatingTextSpawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		if(isset($this->id_handlers[$id])){
			foreach($this->id_handlers[$id] as $handler_id){
				$this->handlers[$handler_id]->onSpawn($text, $entity);
			}
		}
	}

	/**
	 * @internal
	 */
	public function onWorldFloatingTextDespawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		if(isset($this->id_handlers[$id])){
			foreach($this->id_handlers[$id] as $handler_id){
				$this->handlers[$handler_id]->onDespawn($text, $entity);
			}
		}
	}

	/**
	 * @internal
	 */
	public function onWorldFloatingTextAdd(WorldInstance $world, int $id, FloatingText $text) : void{
		foreach($this->handlers as $handler_id => $handler){
			if($handler->canHandle($text)){
				$this->id_handlers[$id][$handler_id] = $handler_id;
			}
		}
	}

	/**
	 * @internal
	 */
	public function onWorldFloatingTextUpdate(WorldInstance $world, int $id, FloatingText $text) : void{
		unset($this->id_handlers[$id]);
		foreach($this->handlers as $handler_id => $handler){
			if($handler->canHandle($text)){
				$this->id_handlers[$id][$handler_id] = $handler_id;
			}
		}
	}

	/**
	 * @internal
	 */
	public function onWorldFloatingTextRemove(WorldInstance $world, int $id) : void{
		unset($this->id_handlers[$id]);
	}

	/**
	 * @internal
	 */
	public function onWorldAdd(WorldInstance $world) : void{
		// NOOP
	}

	/**
	 * @internal
	 */
	public function onWorldRemove(WorldInstance $world) : void{
		// NOOP
	}
}