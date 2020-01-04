<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async;

use muqsit\worldstyler\shapes\async\tasks\AsyncCommonShapePasteTask;
use muqsit\worldstyler\shapes\async\tasks\AsyncCommonShapeStackTask;
use muqsit\worldstyler\shapes\CommonShape;
use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class AsyncCommonShape extends CommonShape {

    public function stack(ChunkManager $level, Vector3 $start, Vector3 $increase, int $repetitions, bool $replace_air = true, ?callable $callable = null) : void
    {
        if (!($level instanceof Level)) {
            throw new \InvalidArgumentException("\$level should be an instance of " . Level::class . " in asynchronous classes, got " . get_class($level));
        }

        $chunks = [];

        $caps = $this->selection->getClipboardCaps();
        $minChunkX = ($start->x + ($increase->x * $caps->x)) >> 4;
        $minChunkZ = ($start->z + ($increase->z * $caps->z)) >> 4;
        $maxChunkX = ($start->x + ($increase->x * $caps->x * $repetitions)) >> 4;
        $maxChunkZ = ($start->z + ($increase->z * $caps->z * $repetitions)) >> 4;

        for ($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX) {
            for ($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ) {
                $chunks[] = $level->getChunk($chunkX, $chunkZ, true);
            }
        }

        $task = new AsyncCommonShapeStackTask(CommonShape::fromSelection($this->selection), $level, $chunks, $callable);
        $task->startFrom($start);
        $task->increaseBy($increase);
        $task->repeat($repetitions);
        $task->replaceAir($replace_air);
        $level->getServer()->getAsyncPool()->submitTask($task);
    }

    public function paste(ChunkManager $level, Vector3 $relative_pos, bool $replace_air = true, ?callable $callable = null) : void
    {
        if (!($level instanceof Level)) {
            throw new \InvalidArgumentException("\$level should be an instance of " . Level::class . " in asynchronous classes, got " . get_class($level));
        }

        $chunks = [];

        $caps = $this->selection->getClipboardCaps();
        $minChunkX = $relative_pos->x >> 4;
        $minChunkZ = $relative_pos->z >> 4;
        $maxChunkX = ($relative_pos->x + $caps->x) >> 4;
        $maxChunkZ = ($relative_pos->z + $caps->z) >> 4;

        for ($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX) {
            for ($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ) {
                $chunks[] = $level->getChunk($chunkX, $chunkZ, true);
            }
        }

        $task = new AsyncCommonShapePasteTask(CommonShape::fromSelection($this->selection), $level, $chunks, $callable);
        $task->setRelativePos($relative_pos);
        $task->replaceAir($replace_air);
        $level->getServer()->getAsyncPool()->submitTask($task);
    }
}