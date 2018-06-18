<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use muqsit\worldstyler\shapes\Cuboid;

use pocketmine\block\Block;

class AsyncCuboidSetTask extends AsyncCuboidTask {

    /** @var Block */
    private $block;

    public function setBlock(Block $block) : void
    {
        $this->block = $block;
    }

    public function onRun() : void
    {
        $level = $this->getChunkManager();
        $cuboid = $this->getCuboid();

        $cuboid->set($level, $this->block, [$this, "updateStatistics"]);
        $this->saveChunks($level, $cuboid->pos1, $cuboid->pos2);
    }
}