<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use muqsit\worldstyler\shapes\Cuboid;

use pocketmine\level\Level;

abstract class AsyncCuboidTask extends AsyncChunksChangeTask {

    /** @var string */
    private $cuboid;

    public function __construct(Cuboid $cuboid, Level $level, array $chunks, ?callable $callable = null)
    {
        $this->cuboid = serialize($cuboid);

        $this->setLevel($level);
        $this->setChunks($chunks);
        $this->setCallable($callable);
    }

    protected function getCuboid() : Cuboid
    {
        return unserialize($this->cuboid);
    }
}