<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext;

final class FloatingText{

	public function __construct(
		readonly public string $world,
		readonly public float $x,
		readonly public float $y,
		readonly public float $z,
		readonly public string $line
	){}
}