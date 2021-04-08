<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\world;

use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\FloatingTextEntity;
use InvalidArgumentException;
use pocketmine\world\World;

final class WorldInstance{

	private static function chunkHash(FloatingText $text) : int{
		return World::chunkHash(((int) $text->getX()) >> 4, ((int) $text->getZ()) >> 4);
	}

	private World $world;

	/** @var FloatingText[] */
	private array $texts = [];

	/** @var int[][]|null[][] */
	private array $text_chunks = []; // = [chunkHash => [id => entity_id|null, id2 => entity_id2|null, ...idn => entity_idn|null]]

	public function __construct(World $world){
		$this->world = $world;
	}

	public function getWorld() : World{
		return $this->world;
	}

	/**
	 * @return FloatingText[]
	 */
	public function getAllFloatingTexts() : array{
		return $this->texts;
	}

	/**
	 * @param array<int, FloatingText> $texts
	 * @return void
	 */
	public function load(array $texts) : void{
		foreach($texts as $id => $text){
			$this->add($id, $text);
		}
	}

	public function add(int $id, FloatingText $text) : void{
		if(isset($this->texts[$id])){
			throw new InvalidArgumentException("Tried adding an already existing floating text");
		}

		$this->addInternally($id, $text);
		WorldManager::onWorldFloatingTextAdd($this, $id, $text);

		$this->trySpawningText($id);
	}

	public function remove(int $id) : FloatingText{
		if(!isset($this->texts[$id])){
			throw new InvalidArgumentException("Tried removing a non-existent floating text");
		}

		$text = $this->texts[$id];
		$this->despawnText($id);
		$this->removeInternally($id);
		WorldManager::onWorldFloatingTextRemove($this, $id);
		return $text;
	}

	public function update(int $id, FloatingText $text) : void{
		$this->despawnText($id);
		$this->removeInternally($id);
		$this->addInternally($id, $text);
		$this->trySpawningText($id);
		WorldManager::onWorldFloatingTextUpdate($this, $id, $text);
	}

	private function addInternally(int $id, FloatingText $text) : void{
		$this->texts[$id] = $text;
		$this->text_chunks[self::chunkHash($text)][$id] = null;
	}

	private function removeInternally(int $id) : void{
		unset($this->text_chunks[$chunk_hash = self::chunkHash($this->texts[$id])][$id], $this->texts[$id]);
		if(count($this->text_chunks[$chunk_hash]) === 0){
			unset($this->text_chunks[$chunk_hash]);
		}
	}

	public function trySpawningText(int $id) : bool{
		World::getXZ(self::chunkHash($this->texts[$id]), $chunkX, $chunkZ);
		if($this->world->isChunkLoaded($chunkX, $chunkZ)){
			$this->spawnText($id);
			return true;
		}
		return false;
	}

	private function spawnText(int $id) : void{
		$text = $this->texts[$id];

		$entity = new FloatingTextEntity($this->world, $id, $text);
		$entity->addDespawnCallback(function() use($text, $id, $entity) : void{
			$this->text_chunks[self::chunkHash($text)][$id] = null;
			WorldManager::onWorldFloatingTextDespawn($this, $id, $text, $entity);
		});
		$this->text_chunks[self::chunkHash($text)][$id] = $entity->getId();
		$entity->spawnToAll();
		WorldManager::onWorldFloatingTextSpawn($this, $id, $text, $entity);
	}

	private function despawnText(int $id) : bool{
		$entity_id = $this->text_chunks[self::chunkHash($this->texts[$id])][$id];
		if($entity_id !== null){
			$entity = $this->world->getEntity($entity_id);
			if($entity !== null){
				// Not using Entity::flagForDespawn() here because during WorldInstance::update(), a
				// floating text could exist while one with the same floating text ID is flagged for despawn,
				// leading to a race condition as database is updated during Entity::close() which is likely
				// called after a new floating text of the same floating text ID has been indexed into the
				// database.
				$entity->close();
				return true;
			}
		}
		return false;
	}

	public function getText(int $id) : ?FloatingText{
		return $this->texts[$id] ?? null;
	}

	public function getTextEntity(int $id) : ?FloatingTextEntity{
		$entity_id = $this->text_chunks[self::chunkHash($this->texts[$id])][$id];
		if($entity_id !== null){
			$entity = $this->world->getEntity($entity_id);
			if($entity instanceof FloatingTextEntity){
				return $entity;
			}
		}
		return null;
	}

	public function onChunkLoad(int $chunkX, int $chunkZ) : void{
		if(isset($this->text_chunks[$chunk_hash = World::chunkHash($chunkX, $chunkZ)])){
			foreach($this->text_chunks[$chunk_hash] as $id => $entity_id){
				if($entity_id === null){
					$this->spawnText($id);
				}
			}
		}
	}
}