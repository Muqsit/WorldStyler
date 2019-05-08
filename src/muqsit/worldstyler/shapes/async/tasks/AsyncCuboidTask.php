<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use muqsit\worldstyler\shapes\Cuboid;

use pocketmine\world\World;

abstract class AsyncCuboidTask extends AsyncChunksChangeTask {

    /** @var string */
    private $cuboid;

    public function __construct(Cuboid $cuboid, World $world, array $chunks, ?callable $callable = null)
    {
        $this->cuboid = serialize($cuboid);

        $this->setWorld($world);
        $this->setChunks($chunks);
        $this->setCallable($callable);
    }

    protected function getCuboid() : Cuboid
    {
        return unserialize($this->cuboid);
    }
}