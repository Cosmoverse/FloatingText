# FloatingText
A PocketMine-MP plugin that spawns floating texts (aka holograms).

## Usage
Once installed, use `/ft add some &eyellow text &rhere` to spawn a floating text.<br>
Use `/ft near` to list all floating texts near you (it also specifies each floating text's ID).<br>
Use `/ft delete <id>` to delete a floating text.

## API
At the moment, there is no API method available for adding or removing floating texts. This means that all floating texts must be set up manually in the game.
However, the plugin does provide an API to update floating texts by replacing wildcards:

On plugin enable, register a `FloatingTextHandler` instance by calling `FloatingTextHandlerManager::register($handler)`.<br>
The `FloatingTextHandler` consists of three methods — `canHandle(FloatingText)`, `onSpawn(FloatingText, FloatingTextEntity)` and `onDespawn(FloatingText, FloatingTextEntity)`.<br>
If your `canHandle()` returns true for a specific `FloatingText` (`FloatingText` holds world, x, y, z and the floating text string), then your `FloatingTextHandler` will be notified via `onSpawn` and `onDespawn` methods whenever a floating text gets spawned.
As a utility, the plugin ships with a `FloatingTextFindAndReplaceHandler` and `FloatingTextFindAndReplaceTickerHandler` that can be used as follows:
```php
/** @var Loader $loader */
$manager = $loader->getHandlerManager();

// FloatingTextFindAndReplaceHandler - Used to update static wildcards.
$manager->register(new FloatingTextFindAndReplaceHandler(
	"{MCPE_VERSION}",
	Server::getInstance()->getVersion()
));
// run /ft add &eServer Version: &l{MCPE_VERSION}
```
```php
// FloatingTextFindAndReplaceTickerHandler - Used to update wildcards repetitively.
$start = time();
$manager->register(new FloatingTextFindAndReplaceTickerHandler(
	$plugin,
	"{LAST_ENVOY}",
	fn() => gmdate("i:s", time() - $start),
	20 * 10 // update every 10 seconds
));
// run /ft add &bLast envoy was {LAST_ENVOY} ago
```

## Performance and Resource Consumption
Once a world is loaded, the plugin caches all floating texts present in that world into memory, indexing them by their unique IDs.
Floating texts are implemented as entities and will only stay loaded as long as the chunk they are located in remains loaded.<br>
FloatingText also maintains a `chunk -> [id -> entity_id]` mapping to speed up floating text lookup.<br>
When registering a `FloatingTextHandler`, the plugin calls `FloatingTextHandler::canHandle()` for all loaded floating texts to prepare a list of floating texts that require an update during runtime.

The floating texts are stored in an `SQLite3` database, and all access to the database is done in a separate thread asynchronously.
This means that when a world is loaded, the floating texts will not be displayed until the database has returned the necessary information.