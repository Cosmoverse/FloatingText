<?php

declare(strict_types=1);

namespace cosmicpe\floatingtext\db;

use Closure;
use cosmicpe\floatingtext\FloatingText;
use cosmicpe\floatingtext\Loader;
use cosmicpe\floatingtext\world\WorldManager;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

final class Database{

	private const INIT = "floatingtexts.init";
	private const LOAD = "floatingtexts.load";
	private const ADD = "floatingtexts.add";
	private const UPDATE = "floatingtexts.update";
	private const REMOVE = "floatingtexts.remove";

	private DataConnector $connector;

	public function __construct(Loader $loader){
		$this->connector = libasynql::create($loader, [
			"type" => "sqlite",
			"sqlite" => [
				"file" => $loader->getConfig()->get("database-file")
			],
			"worker-limit" => 1
		], ["sqlite" => "db/sqlite.sql"]);
		$this->connector->executeGeneric(self::INIT);
		$loader->getWorldManager()->addListener(new DatabaseWorldListener($this));
	}

	/**
	 * Returns all texts in a world indexed by their database ID.
	 *
	 * @param string $world
	 * @param Closure(array<int, FloatingText>) : void $callback
	 */
	public function load(string $world, Closure $callback) : void{
		$this->connector->executeSelect(self::LOAD, ["world" => $world], static function(array $rows) use($callback) : void{
			$texts = [];
			foreach($rows as ["id" => $id, "world" => $world, "x" => $x, "y" => $y, "z" => $z, "line" => $line]){
				$texts[$id] = new FloatingText($world, $x, $y, $z, $line);
			}
			$callback($texts);
		});
	}

	/**
	 * Adds a floating text and returns it's database ID.
	 *
	 * @param FloatingText $text
	 * @param Closure(int) : void $callback
	 */
	public function add(FloatingText $text, Closure $callback) : void{
		$this->connector->executeInsert(self::ADD, [
			"world" => $text->world,
			"x" => $text->x,
			"y" => $text->y,
			"z" => $text->z,
			"line" => $text->line
		], static function(int $insertId, int $affectedRows) use($callback) : void{ $callback($insertId); });
	}

	/**
	 * Updates an existing floating text's property.
	 *
	 * @param int $id
	 * @param FloatingText $text
	 */
	public function update(int $id, FloatingText $text) : void{
		$this->connector->executeChange(self::UPDATE, [
			"id" => $id,
			"world" => $text->world,
			"x" => $text->x,
			"y" => $text->y,
			"z" => $text->z,
			"line" => $text->line
		]);
	}

	/**
	 * Removes an existing floating text of a specific ID.
	 *
	 * @param int $id
	 */
	public function remove(int $id) : void{
		$this->connector->executeChange(self::REMOVE, ["id" => $id]);
	}

	public function close() : void{
		$this->connector->close();
	}
}