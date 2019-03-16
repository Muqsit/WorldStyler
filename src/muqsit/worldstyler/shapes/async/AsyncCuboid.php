<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async;

use muqsit\worldstyler\shapes\async\tasks\AsyncCuboidCopyTask;
use muqsit\worldstyler\shapes\async\tasks\AsyncCuboidReplaceTask;
use muqsit\worldstyler\shapes\async\tasks\AsyncCuboidSetTask;
use muqsit\worldstyler\shapes\Cuboid;
use muqsit\worldstyler\utils\BlockToBlockMapping;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class AsyncCuboid extends Cuboid {

    public function copy(ChunkManager $level, Vector3 $relative_pos, ?callable $callable = null) : void
    {
        if (!($level instanceof Level)) {
            throw new \InvalidArgumentException("\$level should be an instance of " . Level::class . " in asynchronous classes, got " . get_class($level));
        }

        $task = new AsyncCuboidCopyTask(Cuboid::fromSelection($this->selection), $level, $this->getChunks($level), $callable);
        $task->setRelativePos($relative_pos);
        $level->getServer()->getAsyncPool()->submitTask($task);
    }

    public function set(ChunkManager $level, Block $block, ?callable $callable = null) : void
    {
        if (!($level instanceof Level)) {
            throw new \InvalidArgumentException("\$level should be an instance of " . Level::class . " in asynchronous classes, got " . get_class($level));
        }

        $task = new AsyncCuboidSetTask(Cuboid::fromSelection($this->selection), $level, $this->getChunks($level), $callable);
        $task->setBlock($block);
        $level->getServer()->getAsyncPool()->submitTask($task);
    }

    public function replace(ChunkManager $level, BlockToBlockMapping $mapping, ?callable $callable = null) : void
    {
        if (!($level instanceof Level)) {
            throw new \InvalidArgumentException("\$level should be an instance of " . Level::class . " in asynchronous classes, got " . get_class($level));
        }

        $task = new AsyncCuboidReplaceTask(Cuboid::fromSelection($this->selection), $level, $this->getChunks($level), $callable);
        $task->setMapping($mapping);
        $level->getServer()->getAsyncPool()->submitTask($task);
    }

    private function getChunks(Level $level, bool $create = true) : array
    {
        $chunks = [];

        $minChunkX = $this->pos1->x >> 4;
        $maxChunkX = $this->pos2->x >> 4;
        $minChunkZ = $this->pos1->z >> 4;
        $maxChunkZ = $this->pos2->z >> 4;

        for ($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX) {
            for ($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ) {
                $chunks[] = $level->getChunk($chunkX, $chunkZ, $create);
            }
        }

        return $chunks;
    }
}