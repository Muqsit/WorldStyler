<?php

declare(strict_types=1);
namespace muqsit\worldstyler\utils;

use pocketmine\level\utils\SubChunkIteratorManager;

class BlockIterator extends SubChunkIteratorManager {

    public function moveTo(int $x, int $y, int $z) : bool
    {
        if (parent::moveTo($x, $y, $z)) {
            return true;
        }

        if ($this->currentSubChunk === null) {
            $this->currentSubChunk = $this->level->getChunk($this->currentX, $this->currentZ)->getSubChunk($y >> 4, $this->allocateEmptySubs);
            return true;
        }

        return false;
    }
}