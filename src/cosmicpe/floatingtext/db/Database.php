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

	private DataConnector $connector;

	public function __construct(Loader $loader){
		$this->connector = libasynql::create($loader, [
			"type" => "sqlite",
			"sqlite" => [
				"file" => $loader->getConfig()->get("database-file")
			],
			"worker-limit" => 1
		], ["sqlite" => "db/sqlite.sql"]);

		$this->connector->executeGeneric(DatabaseStmts::INIT);
		$this->waitAll();

		WorldManager::addListener(new DatabaseWorldListener($this));
	}

	public function waitAll() : void{
		$this->connector->waitAll();
	}

	/**
	 * Returns all texts in a world indexed by their database ID.
	 *
	 * @param string $world
	 * @param Closure(array<int, FloatingText>) : void $callback
	 */
	public function load(string $world, Closure $callback) : void{
		$this->connector->executeSelect(DatabaseStmts::LOAD, ["world" => $world], static function(array $rows) use($callback) : void{
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
		$this->connector->executeInsert(DatabaseStmts::ADD, [
			"world" => $text->getWorld(),
			"x" => $text->getX(),
			"y" => $text->getY(),
			"z" => $text->getZ(),
			"line" => $text->getLine()
		], static function(int $insertId, int $affectedRows) use($callback) : void{ $callback($insertId); });
	}

	/**
	 * Updates an existing floating text's property.
	 *
	 * @param int $id
	 * @param FloatingText $text
	 */
	public function update(int $id, FloatingText $text) : void{
		$this->connector->executeChange(DatabaseStmts::UPDATE, [
			"id" => $id,
			"world" => $text->getWorld(),
			"x" => $text->getX(),
			"y" => $text->getY(),
			"z" => $text->getZ(),
			"line" => $text->getLine()
		]);
	}

	/**
	 * Removes an existing floating text of a specific ID.
	 *
	 * @param int $id
	 */
	public function remove(int $id) : void{
		$this->connector->executeChange(DatabaseStmts::REMOVE, ["id" => $id]);
	}

	public function close() : void{
		$this->connector->close();
	}
}