<?php

declare(strict_types=1);
namespace muqsit\worldstyler\utils;

use pocketmine\block\Block;

class BlockToBlockMapping {

    /** @var Block[] */
    private $mappings;

    public function add(Block $find, Block $replace) : BlockToBlockMapping
    {
        $this->mappings[] = [$find, $replace];
        return $this;
    }

    public function toFullBlock() : array
    {
        $map = [];

        foreach ($this->mappings as [$find, $replace]) {
            $map[$find->getFullId()] = $replace->getFullId();
        }

        return $map;
    }
}