<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\handler;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;
use cosmicpe\floatingtext\world\WorldManager;

final class FloatingTextHandlerManager{

	/** @var FloatingTextHandler[] */
	private static $handlers = [];

	/** @var int[][] */
	private static $id_handlers = [];

	public static function init() : void{
		WorldManager::addListener(new FloatingTextHandlerWorldListener());
	}

	public static function register(FloatingTextHandler $handler) : void{
		self::$handlers[$handler_id = spl_object_id($handler)] = $handler;
		foreach(WorldManager::getAll() as $world){
			foreach($world->getAllFloatingTexts() as $id => $text){
				if($handler->canHandle($text)){
					self::$id_handlers[$id][$handler_id] = $handler_id;
					$entity = $world->getTextEntity($id);
					if($entity !== null){
						$handler->onSpawn($text, $entity);
					}
				}
			}
		}
	}

	public static function onWorldFloatingTextSpawn(int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		if(isset(self::$id_handlers[$id])){
			foreach(self::$id_handlers[$id] as $handler_id){
				self::$handlers[$handler_id]->onSpawn($text, $entity);
			}
		}
	}

	public static function onWorldFloatingTextDespawn(int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		if(isset(self::$id_handlers[$id])){
			foreach(self::$id_handlers[$id] as $handler_id){
				self::$handlers[$handler_id]->onDespawn($text, $entity);
			}
		}
	}

	public static function onWorldFloatingTextAdd(int $id, FloatingText $text) : void{
		foreach(self::$handlers as $handler_id => $handler){
			if($handler->canHandle($text)){
				self::$id_handlers[$id][$handler_id] = $handler_id;
			}
		}
	}

	public static function onWorldFloatingTextUpdate(int $id, FloatingText $text) : void{
		unset(self::$id_handlers[$id]);
		foreach(self::$handlers as $handler_id => $handler){
			if($handler->canHandle($text)){
				self::$id_handlers[$id][$handler_id] = $handler_id;
			}
		}
	}

	public static function onWorldFloatingTextRemove(int $id) : void{
		unset(self::$id_handlers[$id]);
	}
}