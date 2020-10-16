<?php

declare(strict_types=1);
namespace muqsit\worldstyler\utils;

use pocketmine\world\utils\SubChunkExplorer;

class BlockIterator extends SubChunkExplorer {

    public function moveTo(int $x, int $y, int $z, bool $create = true) : bool
    {
        if (parent::moveTo($x, $y, $z, $create)) {
            return true;
        }

        if ($this->currentSubChunk === null) {
            $this->currentSubChunk = $this->world->getOrLoadChunk($this->currentX, $this->currentZ, $create)->getSubChunk($y >> 4);
            return true;
        }

        return false;
    }
}