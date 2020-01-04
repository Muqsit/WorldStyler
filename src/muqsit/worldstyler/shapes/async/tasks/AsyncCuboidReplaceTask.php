<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use pocketmine\block\Block;

class AsyncCuboidReplaceTask extends AsyncCuboidTask {

    /** @var Block */
    private $find;

    /** @var Block */
    private $replace;

    public function find(Block $block) : void
    {
        $this->find = $block;
    }

    public function replace(Block $block) : void
    {
        $this->replace = $block;
    }

    public function onRun() : void
    {
        $level = $this->getChunkManager();
        $cuboid = $this->getCuboid();

        $cuboid->replace($level, $this->find, $this->replace, [$this, "updateStatistics"]);
        $this->saveChunks($level, $cuboid->pos1, $cuboid->pos2);
    }
}