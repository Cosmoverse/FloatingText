<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\world;

use pocketmine\event\Listener;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;

final class WorldEventListener implements Listener{

	public function __construct(){
	}

	/**
	 * @param WorldLoadEvent $event
	 * @priority LOWEST
	 */
	public function onWorldLoad(WorldLoadEvent $event) : void{
		WorldManager::add($event->getWorld());
	}

	/**
	 * @param WorldUnloadEvent $event
	 * @priority LOWEST
	 */
	public function onWorldUnload(WorldUnloadEvent $event) : void{
		WorldManager::remove($event->getWorld());
	}

	/**
	 * @param ChunkLoadEvent $event
	 * @priority LOWEST
	 */
	public function onChunkLoad(ChunkLoadEvent $event) : void{
		WorldManager::get($event->getWorld())->onChunkLoad($event->getChunkX(), $event->getChunkZ());
	}
}