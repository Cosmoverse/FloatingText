-- #!sqlite
-- #{ floatingtexts

-- #  { init
CREATE TABLE IF NOT EXISTS floating_texts(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  world TEXT NOT NULL,
  x FLOAT NOT NULL,
  y FLOAT NOT NULL,
  z FLOAT NOT NULL,
  line TEXT NOT NULL
);
-- #  }

-- #  { load
-- #    :world string
SELECT id, world, x, y, z, line FROM floating_texts WHERE world=:world;
-- #  }

-- #  { add
-- #    :world string
-- #    :x float
-- #    :y float
-- #    :z float
-- #    :line string
INSERT INTO floating_texts(world, x, y, z, line) VALUES(:world, :x, :y, :z, :line);
-- #  }

-- #  { update
-- #    :id int
-- #    :world string
-- #    :x float
-- #    :y float
-- #    :z float
-- #    :line string
UPDATE floating_texts SET world=:world, x=:x, y=:y, z=:z, line=:line WHERE id=:id;
-- #  }

-- #  { remove
-- #    :id int
DELETE FROM floating_texts WHERE id=:id;
-- #  }
-- #}