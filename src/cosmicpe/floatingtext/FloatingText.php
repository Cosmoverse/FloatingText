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

	public function getX() : float{
		return $this->x;
	}

	public function getY() : float{
		return $this->y;
	}

	public function getZ() : float{
		return $this->z;
	}

	public function getLine() : string{
		return $this->line;
	}
}