<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes;

use muqsit\worldstyler\Selection;
use muqsit\worldstyler\shapes\async\AsyncCuboid;
use muqsit\worldstyler\utils\BlockIterator;
use muqsit\worldstyler\utils\Utils;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class Cuboid {

    public static function fromSelection(Selection $selection) : Cuboid
    {
        return new Cuboid($selection->getPosition(1), $selection->getPosition(2), $selection);
    }

    /** @var Vector3 */
    public $pos1;

    /** @var Vector3 */
    public $pos2;

    /** @var Selection */
    public $selection;

    public function __construct(Vector3 $pos1, Vector3 $pos2, Selection $selection)
    {
        $minX = min($pos1->x, $pos2->x);
        $maxX = max($pos1->x, $pos2->x);
        $minY = min($pos1->y, $pos2->y);
        $maxY = max($pos1->y, $pos2->y);
        $minZ = min($pos1->z, $pos2->z);
        $maxZ = max($pos1->z, $pos2->z);

        $pos1->x = $minX;
        $pos1->y = $minY;
        $pos1->z = $minZ;

        $pos2->x = $maxX;
        $pos2->y = $maxY;
        $pos2->z = $maxZ;

        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
        $this->selection = $selection;
    }

    public function copy(ChunkManager $level, Vector3 $relative_pos, ?callable $callable = null) : void
    {
        $time = microtime(true);

        $s_pos = $this->pos1->subtract($relative_pos->floor());

        $cap = $this->pos2->subtract($this->pos1);
        $xCap = $cap->x;
        $yCap = $cap->y;
        $zCap = $cap->z;

        $minX = $this->pos1->x;
        $minY = $this->pos1->y;
        $minZ = $this->pos1->z;

        $blocks = [];
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

        $this->selection->setClipboard($blocks, $s_pos, $cap);

        $time = microtime(true) - $time;
        if ($callable !== null) {
            $callable($time, ($xCap + 1) * ($yCap + 1) * ($zCap + 1));
        }
    }

    public function set(ChunkManager $level, Block $block, ?callable $callable = null) : void
    {
        $time = microtime(true);

        $minX = $this->pos1->x;
        $maxX = $this->pos2->x;
        $minY = $this->pos1->y;
        $maxY = $this->pos2->y;
        $minZ = $this->pos1->z;
        $maxZ = $this->pos2->z;

        $blockId = $block->getId();
        $blockMeta = $block->getMeta();

        $iterator = new BlockIterator($level);

        for ($x = $minX; $x <= $maxX; ++$x) {
            for ($z = $minZ; $z <= $maxZ; ++$z) {
                for ($y = $minY; $y <= $maxY; ++$y) {
                    $iterator->moveTo($x, $y, $z);
                    $iterator->currentSubChunk->setBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $blockId, $blockMeta);
                }
            }
        }

        if ($level instanceof Level) {
            Utils::updateChunks($level, $minX >> 4, $maxX >> 4, $minZ >> 4, $maxZ >> 4);
        }

        $time = microtime(true) - $time;
        if ($callable !== null) {
            $callable($time, (1 + $maxX - $minX) * (1 + $maxY - $minY) * (1 + $maxZ - $minZ));
        }
    }

    public function replace(ChunkManager $level, Block $find, Block $replace, ?callable $callable = null) : void
    {
        $time = microtime(true);

        $minX = $this->pos1->x;
        $maxX = $this->pos2->x;
        $minY = $this->pos1->y;
        $maxY = $this->pos2->y;
        $minZ = $this->pos1->z;
        $maxZ = $this->pos2->z;

        $find = ($find->getId() << 4) | $find->getMeta();//fullBlock

        $replaceId = $replace->getId();
        $replaceMeta = $replace->getMeta();

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

        if ($level instanceof Level) {
            Utils::updateChunks($level, $minX >> 4, $maxX >> 4, $minZ >> 4, $maxZ >> 4);
        }

        $time = microtime(true) - $time;
        if ($callable !== null) {
            $callable($time, (1 + $maxX - $minX) * (1 + $maxY - $minY) * (1 + $maxZ - $minZ));
        }
    }

    public function async() : AsyncCuboid
    {
        return new AsyncCuboid($this->pos1, $this->pos2, $this->selection);
    }
}
