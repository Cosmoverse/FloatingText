<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext;

use Closure;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\World;

class FloatingTextEntity extends Entity{

	public static function getNetworkTypeId() : string{
		return EntityIds::FALLING_BLOCK;
	}

	public $height = 0.0;
	public $width = 0.0;
	public $gravity = 0.0;
	public $canCollide = false;
	public $keepMovement = true;
	protected $gravityEnabled = false;
	protected $drag = 0.0;
	protected $scale = 0.0;
	protected $immobile = true;

	/** @var float */
	protected $baseOffset = 0.49;

	/** @var int */
	private $floating_text_id;

	/** @var FloatingText */
	private $floating_text;

	/** @var Closure[] */
	private $despawn_callbacks = [];

	public function __construct(World $world, int $text_id, FloatingText $text){
		$this->setCanSaveWithChunk(false);
		$this->floating_text_id = $text_id;
		$this->floating_text = $text;
		parent::__construct(new Location($text->getX(), $text->getY(), $text->getZ(), 0.0, 0.0, $world));
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->setNameTag($this->floating_text->getLine());
		$this->setNameTagAlwaysVisible(true);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);
		$properties->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, $this->alwaysShowNameTag ? 1 : 0);
		$properties->setFloat(EntityMetadataProperties::SCALE, $this->scale);
		$properties->setString(EntityMetadataProperties::NAMETAG, $this->nameTag);
		$properties->setGenericFlag(EntityMetadataFlags::IMMOBILE, $this->immobile);
		$properties->setInt(EntityMetadataProperties::VARIANT, RuntimeBlockMapping::getInstance()->toRuntimeId(VanillaBlocks::AIR()->getFullId()));
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

	protected function checkBlockCollision() : void{
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function canBeMovedByCurrents() : bool{
		return false;
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return parent::getOffsetPosition($vector3)->add(0.0, $this->baseOffset, 0.0);
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
		$this->sendData($this->hasSpawned, $this->getSyncedNetworkData(true));
		$this->getNetworkProperties()->clearDirtyProperties();
	}

	protected function onDispose() : void{
		parent::onDispose();
		foreach($this->despawn_callbacks as $callback){
			$callback();
		}
	}

	protected function destroyCycles() : void{
		parent::destroyCycles();
		$this->despawn_callbacks = [];
	}
}