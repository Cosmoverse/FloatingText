<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\db;

interface DatabaseStmts{

	public const INIT = "floatingtexts.init";
	public const LOAD = "floatingtexts.load";
	public const ADD = "floatingtexts.add";
	public const UPDATE = "floatingtexts.update";
	public const REMOVE = "floatingtexts.remove";
}