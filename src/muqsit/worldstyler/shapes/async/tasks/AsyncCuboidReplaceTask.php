<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use muqsit\worldstyler\shapes\Cuboid;
use muqsit\worldstyler\utils\BlockToBlockMapping;

use pocketmine\block\Block;

class AsyncCuboidReplaceTask extends AsyncCuboidTask {

    /** @var BlockToBlockMapping */
    private $mapping;

    public function setMapping(BlockToBlockMapping $mapping) : void
    {
        $this->mapping = $mapping;
    }

    public function onRun() : void
    {
        $level = $this->getChunkManager();
        $cuboid = $this->getCuboid();

        $cuboid->replace($level, $this->mapping, [$this, "updateStatistics"]);
        $this->saveChunks($level, $cuboid->pos1, $cuboid->pos2);
    }
}