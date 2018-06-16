<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes;

use muqsit\worldstyler\Selection;
use muqsit\worldstyler\utils\BlockIterator;
use muqsit\worldstyler\utils\Utils;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class Cuboid {

    /** @var Vector3 */
    private $pos1;

    /** @var Vector3 */
    private $pos2;

    /** @var Selection */
    private $selection;

    public function __construct(Selection $selection)
    {
        $this->pos1 = $selection->getPosition(1);
        $this->pos2 = $selection->getPosition(2);
        $this->selection = $selection;
    }

    public function copy(Vector3 $relative_pos, Level $level, &$time = null) : int
    {
        $time = microtime(true);

        $minX = min($this->pos1->x, $this->pos2->x);
        $maxX = max($this->pos1->x, $this->pos2->x);
        $minY = min($this->pos1->y, $this->pos2->y);
        $maxY = max($this->pos1->y, $this->pos2->y);
        $minZ = min($this->pos1->z, $this->pos2->z);
        $maxZ = max($this->pos1->z, $this->pos2->z);

        $pos = new Vector3($minX, $minY, $minZ);
        $s_pos = $pos->subtract($relative_pos->floor());

        $blocks = [];

        $xCap = $maxX - $minX;
        $zCap = $maxZ - $minZ;
        $yCap = $maxY - $minY;

        $iterator = new BlockIterator($level);

        for ($x = 0; $x <= $xCap; ++$x) {
            $ax = $minX + $x;
            for ($z = 0; $z <= $zCap; ++$z) {
                $az = $minZ + $z;
                for ($y = 0; $y <= $yCap; ++$y) {
                    $ay = $minY + $y;
                    $iterator->moveTo($ax, $ay, $az);
                    $blocks[Level::blockHash($x, $y, $z)] = $iterator->currentSubChunk->getFullBlock($ax & 0x0f, $ay & 0x0f, $az & 0x0f);
                }
            }
        }

        $this->selection->setClipboard($blocks, $s_pos, new Vector3($xCap, $yCap, $zCap));

        $time = microtime(true) - $time;
        return ($xCap + 1) * ($yCap + 1) * ($zCap + 1);
    }

    public function set(Block $block, Level $level, &$time = null) : int
    {
        $time = microtime(true);

        $minX = min($this->pos1->x, $this->pos2->x);
        $maxX = max($this->pos1->x, $this->pos2->x);
        $minY = min($this->pos1->y, $this->pos2->y);
        $maxY = max($this->pos1->y, $this->pos2->y);
        $minZ = min($this->pos1->z, $this->pos2->z);
        $maxZ = max($this->pos1->z, $this->pos2->z);

        $blockId = $block->getId();
        $blockMeta = $block->getDamage();

        $iterator = new BlockIterator($level);

        for ($x = $minX; $x <= $maxX; ++$x) {
            for ($z = $minZ; $z <= $maxZ; ++$z) {
                for ($y = $minY; $y <= $maxY; ++$y) {
                    $iterator->moveTo($x, $y, $z);
                    $iterator->currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $blockId, $blockMeta);
                }
            }
        }

        Utils::updateChunks($level, $minX >> 4, $maxX >> 4, $minZ >> 4, $maxZ >> 4);

        $time = microtime(true) - $time;
        return (1 + $maxX - $minX) * (1 + $maxY - $minY) * (1 + $maxZ - $minZ);
    }

    public function replace(Block $find, Block $replace, Level $level, &$time = null) : int
    {
        $time = microtime(true);

        $minX = min($this->pos1->x, $this->pos2->x);
        $maxX = max($this->pos1->x, $this->pos2->x);
        $minY = min($this->pos1->y, $this->pos2->y);
        $maxY = max($this->pos1->y, $this->pos2->y);
        $minZ = min($this->pos1->z, $this->pos2->z);
        $maxZ = max($this->pos1->z, $this->pos2->z);

        $find = ($find->getId() << 4) | $find->getDamage();//fullBlock

        $replaceId = $replace->getId();
        $replaceMeta = $replace->getDamage();

        $iterator = new BlockIterator($level);

        for ($x = $minX; $x <= $maxX; ++$x) {
            for ($z = $minZ; $z <= $maxZ; ++$z) {
                for ($y = $minY; $y <= $maxY; ++$y) {
                    $iterator->moveTo($x, $y, $z);
                    if ($iterator->currentSubChunk->getFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f) === $find) {
                        $iterator->currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $replaceId, $replaceMeta);
                    }
                }
            }
        }

        Utils::updateChunks($level, $minX >> 4, $maxX >> 4, $minZ >> 4, $maxZ >> 4);

        $time = microtime(true) - $time;
        return (1 + $maxX - $minX) * (1 + $maxY - $minY) * (1 + $maxZ - $minZ);
    }
}