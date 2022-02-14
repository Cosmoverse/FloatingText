<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext;

final class FloatingText{

	public function __construct(
		private string $world,
		private float $x,
		private float $y,
		private float $z,
		private string $line
	){}

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