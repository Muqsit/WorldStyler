<?php

declare(strict_types=1);
namespace muqsit\worldstyler\schematics\async;

use muqsit\worldstyler\schematics\async\tasks\AsyncSchematicPasteTask;
use muqsit\worldstyler\schematics\Schematic;

use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class AsyncSchematic extends Schematic {

    public function paste(ChunkManager $level, Vector3 $relative_pos, bool $replace_pc_blocks = true, ?callable $callable = null) : void
    {
        if (!($level instanceof Level)) {
            throw new \InvalidArgumentException("\$level should be an instance of " . Level::class . " in asynchronous classes, got " . get_class($level));
        }

        $task = new AsyncSchematicPasteTask($relative_pos, $this->file, $replace_pc_blocks, $callable);
        $task->setLevel($level);
        $level->getServer()->getAsyncPool()->submitTask($task);
    }
}