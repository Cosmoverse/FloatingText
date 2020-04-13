# FloatingText
A PocketMine-MP plugin that spawns floating texts (aka holograms).

## Usage
Once installed, use `/ft add some &eyellow text &rhere` to spawn a floating text.<br>
Use `/ft near` to list all floating texts near you (it also specifies each floating text's ID).<br>
Use `/ft delete <id>` to delete a floating text.

## API
Currently, there's no API to add/remove floating texts (or if you wish to: `Server::dispatchCommand()` is always a possibility).
There is however a pretty optimized floating text handling API aimed at updating floating texts by replacing wildcards.<br>

Preferably on plugin enable, you can register a `FloatingTextHandler` instance by calling `FloatingTextHandlerManager::register($handler)`.<br>
The `FloatingTextHandler` consists of three methods — `canHandle(FloatingText)`, `onSpawn(FloatingText, FloatingTextEntity)` and `onDespawn(FloatingText, FloatingTextEntity)`.<br>
If your `canHandle()` returns true for a specific `FloatingText` (`FloatingText` holds world, x, y, z and the floating text string), then your `FloatingTextHandler` will be notified via `onSpawn` and `onDespawn` methods whenever a floating text gets spawned.
This is the base information required for implementation of a runtime-efficient find-and-replace-wildcard-in-floating-text.<br>
As a utility, the plugin ships with a `FloatingTextFindAndReplaceHandler` and `FloatingTextFindAndReplaceTickerHandler` that can be used like so:
```php
// FloatingTextFindAndReplaceHandler - Used to update static wildcards.
FloatingTextHandlerManager::register(new FloatingTextFindAndReplaceHandler(
	"{MCPE_VERSION}",
	Server::getInstance()->getVersion()
));
// run /ft add &eServer Version: &l{MCPE_VERSION}
```
```php
// FloatingTextFindAndReplaceTickerHandler - Used to update wildcards repetitively.
$start = time();
FloatingTextHandlerManager::register(new FloatingTextFindAndReplaceTickerHandler(
	$plugin,
	"{LAST_ENVOY}",
	function() use($start) : string{
		return gmdate("i:s", time() - $start);
	},
	20 * 10 // update every 10 seconds
));
// run /ft add &bLast envoy was {LAST_ENVOY} ago
```

## Performance and Resource Consumption
Once a world is loaded, FloatingText will cache all floating texts into memory, indexing them by their unique IDs.
FloatingText also maintains a chunk -> [id -> entity_id] mapping to speed up floating texts lookup on chunk basis.<br>
The plugin was optimized for fast runtime lookups by sacrificing memory (and in some cases, CPU too (yeah i know)).<br>
While registering a `FloatingTextHandler`, the plugin loops through all cached floating texts and calls `FloatingTextHandler::canHandle()` so
it can prepare a list of floating texts that require an update during runtime, making `FloatingTextHandlerManager::register()` `O(n)` `n = number of cached floating texts / sum of number of floating texts in all loaded worlds`.<br>

The floating texts are stored in an `SQLite3` database and all calls to the database are tbreaded + asynchronous (except for `CREATE TABLE` which sleeps the main thread until the query has been executed).
This means whenever a world is loaded, the floating texts may not appear until the results of the SELECT statement are returned. However, SQLite3 is fast enough to make this a near impossible case (unless you hacked the plugin and got access to the `Database` object and are hogging the database with mass insert/updates requests).
