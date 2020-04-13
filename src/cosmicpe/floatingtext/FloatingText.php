<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext;

final class FloatingText{

	/** @var string */
	private $world;

	/** @var float */
	private $x;

	/** @var float */
	private $y;

	/** @var float */
	private $z;

	/** @var string */
	private $line;

	public function __construct(string $world, float $x, float $y, float $z, string $line){
		$this->world = $world;
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->line = $line;
	}

	public function getWorld() : string{
		return $this->world;
	}

	public function setWorld(string $world) : void{
		$this->world = $world;
	}

	public function getX() : float{
		return $this->x;
	}

	public function getY() : float{
		return $this->y;
	}

	public function getZ() : float{
		return $this->z;
	}

	public function setPosition(float $x, float $y, float $z) : void{
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
	}

	public function getLine() : string{
		return $this->line;
	}

	public function setLine(string $line) : void{
		$this->line = $line;
	}
}