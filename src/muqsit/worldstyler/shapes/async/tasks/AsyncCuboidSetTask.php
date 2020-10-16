<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

class AsyncCuboidSetTask extends AsyncCuboidTask {

    /** @var int */
    private $blockFullId;

    public function setBlock(int $fullId) : void
    {
        $this->blockFullId = $fullId;
    }

    public function onRun() : void
    {
        $world = $this->getChunkManager();
        $cuboid = $this->getCuboid();

        $cuboid->set($world, $this->blockFullId, [$this, "updateStatistics"]);
        $this->saveChunks($world, $cuboid->pos1, $cuboid->pos2);
    }
}