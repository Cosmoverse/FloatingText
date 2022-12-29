<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\world;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;
use cosmicpe\floatingtext\Loader;
use pocketmine\world\World;

final class WorldManager{

	/** @var array<int, WorldInstance> */
	private array $worlds = [];

	/** @var array<int, WorldListener> */
	private array $listeners = [];

	public function __construct(){
	}

	public function init(Loader $loader) : void{
		foreach($loader->getServer()->getWorldManager()->getWorlds() as $world){
			$this->add($world);
		}

		$loader->getServer()->getPluginManager()->registerEvents(new WorldEventListener($this), $loader);
	}

	public function addListener(WorldListener $listener) : void{
		$this->listeners[spl_object_id($listener)] = $listener;
	}

	public function removeListener(WorldListener $listener) : void{
		unset($this->listeners[spl_object_id($listener)]);
	}

	public function add(World $world) : void{
		$this->worlds[$world->getId()] = $instance = new WorldInstance($this, $world);
		foreach($world->getLoadedChunks() as $chunk_hash => $_){
			World::getXZ($chunk_hash, $chunkX, $chunkZ);
			$instance->onChunkLoad($chunkX, $chunkZ);
		}
		foreach($this->listeners as $listener){
			$listener->onWorldAdd($instance);
		}
	}

	public function remove(World $world) : void{
		$instance = $this->worlds[$id = $world->getId()];
		unset($this->worlds[$id]);
		foreach($this->listeners as $listener){
			$listener->onWorldRemove($instance);
		}
	}

	public function get(World $world) : WorldInstance{
		return $this->worlds[$world->getId()];
	}

	/**
	 * @return array<int, WorldInstance>
	 */
	public function getAll() : array{
		return $this->worlds;
	}

	public function onWorldFloatingTextAdd(WorldInstance $world, int $id, FloatingText $text) : void{
		foreach($this->listeners as $listener){
			$listener->onWorldFloatingTextAdd($world, $id, $text);
		}
	}

	public function onWorldFloatingTextUpdate(WorldInstance $world, int $id, FloatingText $text) : void{
		foreach($this->listeners as $listener){
			$listener->onWorldFloatingTextUpdate($world, $id, $text);
		}
	}

	public function onWorldFloatingTextSpawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		foreach($this->listeners as $listener){
			$listener->onWorldFloatingTextSpawn($world, $id, $text, $entity);
		}
	}

	public function onWorldFloatingTextDespawn(WorldInstance $world, int $id, FloatingText $text, FloatingTextEntity $entity) : void{
		foreach($this->listeners as $listener){
			$listener->onWorldFloatingTextDespawn($world, $id, $text, $entity);
		}
	}

	public function onWorldFloatingTextRemove(WorldInstance $world, int $id) : void{
		foreach($this->listeners as $listener){
			$listener->onWorldFloatingTextRemove($world, $id);
		}
	}
}