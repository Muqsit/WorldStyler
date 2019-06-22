<?php

declare(strict_types=1);
namespace muqsit\worldstyler\utils;

use pocketmine\block\Block;

class BlockToBlockMapping {

    /** @var int[] */
    private $mappings;

    public function add(Block $find, Block $replace) : BlockToBlockMapping
    {
        $this->mappings[$find->getFullId()] = $replace->getFullId();
        return $this;
    }

    public function toFullBlock() : array
    {
        return $this->mappings;
    }
}