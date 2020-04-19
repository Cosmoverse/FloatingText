<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\world;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;
use cosmicpe\floatingtext\Loader;
use pocketmine\world\World;

final class WorldManager{

	/** @var WorldInstance[] */
	private static $worlds = [];

	/** @var WorldListener[] */
	private static $listeners = [];

	public static function init(Loader $loader) : void{
		foreach($loader->getServer()->getWorldManager()->getWorlds() as $world){
			self::add($world);
		}

		$loader->getServer()->getPluginManager()->registerEvents(new WorldEventListener(), $loader);
	}

	public static function addListener(WorldListener $listener) : void{
		self::$listeners[spl_object_id($listener)] = $listener;
	}

	public static function removeListener(WorldListener $listener) : void{
		unset(self::$listeners[spl_object_id($listener)]);
	}

	public static function add(World $world) : void{
		self::$worlds[$world->getId()] = $instance = new WorldInstance($world);
		foreach($world->getChunks() as $chunk){
			$instance->onChunkLoad($chunk->getX(), $chunk->getZ());
		}
		foreach(self::$listeners as $listener){
			$listener->onWorldAdd($instance);
		}
	}

	public static function remove(World $world) : void{
		$instance = self::$worlds[$id = $world->getId()];
		unset(self::$worlds[$id]);
		foreach(self::$listeners as $listener){
			$listener->onWorldRemove($instance);
		}
	}

	public static function get(World $world) : WorldInstance{
		return self::$worlds[$world->getId()];
	}

	/**
	 * @return WorldInstance[]
	 */
	public static function getAll() : array{
		return self::$worlds;
	}

	public static function onWorldFloatingTextAdd(WorldInstance $world, int $id, FloatingText $text) : void{
		foreach(self::$listeners as $listener){
			$listener->onWorldFloatingTextAdd($world, $id, $text);
		}
	}

	public static function onWorldFloatingTextUpdate(WorldInstance $world, int $id, FloatingText $text) : void{
		foreach(self::$listeners as $listener){
			$listener->onWorldFloatingTextUpdate($world, $id, $text);
		}
	}

	public static function onWorldFloatingTextSpawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		foreach(self::$listeners as $listener){
			$listener->onWorldFloatingTextSpawn($world, $id, $text, $entity);
		}
	}

	public static function onWorldFloatingTextDespawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		foreach(self::$listeners as $listener){
			$listener->onWorldFloatingTextDespawn($world, $id, $text, $entity);
		}
	}

	public static function onWorldFloatingTextRemove(WorldInstance $world, int $id) : void{
		foreach(self::$listeners as $listener){
			$listener->onWorldFloatingTextRemove($world, $id);
		}
	}
}