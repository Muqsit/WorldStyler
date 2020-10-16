<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async;

use muqsit\worldstyler\shapes\async\tasks\AsyncCommonShapeStackTask;
use muqsit\worldstyler\shapes\async\tasks\AsyncCommonShapePasteTask;
use muqsit\worldstyler\shapes\CommonShape;

use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use pocketmine\math\Vector3;

class AsyncCommonShape extends CommonShape {

    public function stack(ChunkManager $world, Vector3 $start, Vector3 $increase, int $repetitions, bool $replace_air = true, ?callable $callable = null) : void
    {
        if (!($world instanceof World)) {
            throw new \InvalidArgumentException("\$world should be an instance of " . World::class . " in asynchronous classes, got " . get_class($world));
        }

        $chunks = [];

        $caps = $this->selection->getClipboardCaps();
        $minChunkX = ($start->x + ($increase->x * $caps->x)) >> 4;
        $minChunkZ = ($start->z + ($increase->z * $caps->z)) >> 4;
        $maxChunkX = ($start->x + ($increase->x * $caps->x * $repetitions)) >> 4;
        $maxChunkZ = ($start->z + ($increase->z * $caps->z * $repetitions)) >> 4;

        for ($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX) {
            for ($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ) {
                $chunks[] = $world->getOrLoadChunk($chunkX, $chunkZ, true);
            }
        }

        $task = new AsyncCommonShapeStackTask(CommonShape::fromSelection($this->selection), $world, $chunks, $callable);
        $task->startFrom($start);
        $task->increaseBy($increase);
        $task->repeat($repetitions);
        $task->replaceAir($replace_air);
        $world->getServer()->getAsyncPool()->submitTask($task);
    }

    public function paste(ChunkManager $world, Vector3 $relative_pos, bool $replace_air = true, ?callable $callable = null) : void
    {
        if (!($world instanceof World)) {
            throw new \InvalidArgumentException("\$world should be an instance of " . World::class . " in asynchronous classes, got " . get_class($world));
        }

        $chunks = [];

        $caps = $this->selection->getClipboardCaps();
        $minChunkX = $relative_pos->x >> 4;
        $minChunkZ = $relative_pos->z >> 4;
        $maxChunkX = ($relative_pos->x + $caps->x) >> 4;
        $maxChunkZ = ($relative_pos->z + $caps->z) >> 4;

        for ($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX) {
            for ($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ) {
                $chunks[] = $world->getOrLoadChunk($chunkX, $chunkZ, true);
            }
        }

        $task = new AsyncCommonShapePasteTask(CommonShape::fromSelection($this->selection), $world, $chunks, $callable);
        $task->setRelativePos($relative_pos);
        $task->replaceAir($replace_air);
        $world->getServer()->getAsyncPool()->submitTask($task);
    }
}