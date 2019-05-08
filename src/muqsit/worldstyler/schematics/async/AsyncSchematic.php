<?php

declare(strict_types=1);
namespace muqsit\worldstyler\schematics\async;

use muqsit\worldstyler\schematics\async\tasks\AsyncSchematicPasteTask;
use muqsit\worldstyler\schematics\Schematic;

use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use pocketmine\math\Vector3;

class AsyncSchematic extends Schematic {

    public function paste(ChunkManager $world, Vector3 $relative_pos, bool $replace_pc_blocks = true, ?callable $callable = null) : void
    {
        if (!($world instanceof World)) {
            throw new \InvalidArgumentException("\$world should be an instance of " . World::class . " in asynchronous classes, got " . get_class($world));
        }

        $task = new AsyncSchematicPasteTask($relative_pos, $this->file, $replace_pc_blocks, $callable);
        $task->setWorld($world);
        $world->getServer()->getAsyncPool()->submitTask($task);
    }
}