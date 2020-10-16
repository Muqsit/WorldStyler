<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async;

use muqsit\worldstyler\shapes\async\tasks\AsyncCuboidCopyTask;
use muqsit\worldstyler\shapes\async\tasks\AsyncCuboidReplaceTask;
use muqsit\worldstyler\shapes\async\tasks\AsyncCuboidSetTask;
use muqsit\worldstyler\shapes\Cuboid;
use muqsit\worldstyler\utils\BlockToBlockMapping;

use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use pocketmine\math\Vector3;

class AsyncCuboid extends Cuboid {

    public function copy(ChunkManager $world, Vector3 $relative_pos, ?callable $callable = null) : void
    {
        if (!($world instanceof World)) {
            throw new \InvalidArgumentException("\$world should be an instance of " . World::class . " in asynchronous classes, got " . get_class($world));
        }

        $task = new AsyncCuboidCopyTask(Cuboid::fromSelection($this->selection), $world, $this->getChunks($world), $callable);
        $task->setRelativePos($relative_pos);
        $world->getServer()->getAsyncPool()->submitTask($task);
    }

    public function set(ChunkManager $world, int $fullId, ?callable $callable = null) : void
    {
        if (!($world instanceof World)) {
            throw new \InvalidArgumentException("\$world should be an instance of " . World::class . " in asynchronous classes, got " . get_class($world));
        }

        $task = new AsyncCuboidSetTask(Cuboid::fromSelection($this->selection), $world, $this->getChunks($world), $callable);
        $task->setBlock($fullId);
        $world->getServer()->getAsyncPool()->submitTask($task);
    }

    public function replace(ChunkManager $world, BlockToBlockMapping $mapping, ?callable $callable = null) : void
    {
        if (!($world instanceof World)) {
            throw new \InvalidArgumentException("\$world should be an instance of " . World::class . " in asynchronous classes, got " . get_class($world));
        }

        $task = new AsyncCuboidReplaceTask(Cuboid::fromSelection($this->selection), $world, $this->getChunks($world), $callable);
        $task->setMapping($mapping);
        $world->getServer()->getAsyncPool()->submitTask($task);
    }

    private function getChunks(World $world, bool $create = true) : array
    {
        $chunks = [];

        $minChunkX = $this->pos1->x >> 4;
        $maxChunkX = $this->pos2->x >> 4;
        $minChunkZ = $this->pos1->z >> 4;
        $maxChunkZ = $this->pos2->z >> 4;

        for ($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX) {
            for ($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ) {
                $chunks[] = $world->getOrLoadChunk($chunkX, $chunkZ, $create);
            }
        }

        return $chunks;
    }
}