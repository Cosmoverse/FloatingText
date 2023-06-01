<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext;

use Closure;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\World;

class FloatingTextEntity extends Entity{

	public static function getNetworkTypeId() : string{
		return EntityIds::FALLING_BLOCK;
	}

	private int $floating_text_id;
	private FloatingText $floating_text;

	/** @var array<int, Closure> */
	private array $despawn_callbacks = [];

	public function __construct(World $world, int $text_id, FloatingText $text){
		$this->setCanSaveWithChunk(false);
		$this->floating_text_id = $text_id;
		$this->floating_text = $text;
		$this->keepMovement = true;
		$this->gravity = 0.0;
		$this->gravityEnabled = false;
		$this->drag = 0.0;
		$this->noClientPredictions = true;
		parent::__construct(new Location($text->x, $text->y, $text->z, $world, 0.0, 0.0));
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->setNameTag($this->floating_text->line);
		$this->setNameTagAlwaysVisible();
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.01, 0.01);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);
		$properties->setInt(EntityMetadataProperties::VARIANT, TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId()));
	}

	public function addDespawnCallback(Closure $callback) : void{
		$this->despawn_callbacks[spl_object_id($callback)] = $callback;
	}

	public function getFloatingTextId() : int{
		return $this->floating_text_id;
	}

	public function getFloatingText() : FloatingText{
		return $this->floating_text;
	}

	public function isFireProof() : bool{
		return true;
	}

	public function canBeCollidedWith() : bool{
		return false;
	}

	protected function checkBlockIntersections() : void{
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function canBeMovedByCurrents() : bool{
		return false;
	}

	protected function getInitialDragMultiplier() : float{
		return 0.0;
	}

	protected function getInitialGravity() : float{
		return 0.0;
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return parent::getOffsetPosition($vector3)->add(0.0, 0.49, 0.0);
	}

	public function attack(EntityDamageEvent $source) : void{
		$source->cancel();
	}

	public function onUpdate(int $currentTick) : bool{
		return false;
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		return false;
	}

	public function setNameTag(string $name) : void{
		parent::setNameTag($name);
		$this->sendData($this->hasSpawned, $this->getDirtyNetworkData());
		$this->getNetworkProperties()->clearDirtyProperties();
	}

	public function executeFloatingTextDespawnHooks() : void{
		foreach($this->despawn_callbacks as $callback){
			$callback();
		}
		$this->despawn_callbacks = [];
	}

	protected function onDispose() : void{
		parent::onDispose();
		$this->executeFloatingTextDespawnHooks();
	}

	protected function destroyCycles() : void{
		parent::destroyCycles();
		unset($this->despawn_callbacks);
	}
}