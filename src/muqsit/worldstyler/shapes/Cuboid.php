<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes;

use muqsit\worldstyler\Selection;
use muqsit\worldstyler\shapes\async\AsyncCuboid;
use muqsit\worldstyler\utils\BlockIterator;
use muqsit\worldstyler\utils\BlockToBlockMapping;
use muqsit\worldstyler\utils\Utils;

use pocketmine\world\ChunkManager;
use pocketmine\world\World;
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

    public function copy(ChunkManager $world, Vector3 $relative_pos, ?callable $callable = null) : void
    {
        $time = microtime(true);

        $s_pos = $this->pos1->subtractVector($relative_pos->floor());

        $cap = $this->pos2->subtractVector($this->pos1);
        $xCap = $cap->x;
        $yCap = $cap->y;
        $zCap = $cap->z;

        $minX = $this->pos1->x;
        $minY = $this->pos1->y;
        $minZ = $this->pos1->z;

        $blocks = [];
        $iterator = new BlockIterator($world);

        for ($x = 0; $x <= $xCap; ++$x) {
            $ax = $minX + $x;
            for ($z = 0; $z <= $zCap; ++$z) {
                $az = $minZ + $z;
                for ($y = 0; $y <= $yCap; ++$y) {
                    $ay = $minY + $y;
                    $iterator->moveTo($ax, $ay, $az);
                    $blocks[World::blockHash($x, $y, $z)] = $iterator->currentSubChunk->getFullBlock($ax & 0x0f, $ay & 0x0f, $az & 0x0f);
                }
            }
        }

        $this->selection->setClipboard($blocks, $s_pos, $cap);

        $time = microtime(true) - $time;
        if ($callable !== null) {
            $callable($time, ($xCap + 1) * ($yCap + 1) * ($zCap + 1));
        }
    }

    public function set(ChunkManager $world, int $fullId, ?callable $callable = null) : void
    {
        $time = microtime(true);

        $minX = $this->pos1->x;
        $maxX = $this->pos2->x;
        $minY = $this->pos1->y;
        $maxY = $this->pos2->y;
        $minZ = $this->pos1->z;
        $maxZ = $this->pos2->z;

        $iterator = new BlockIterator($world);

        for ($x = $minX; $x <= $maxX; ++$x) {
            for ($z = $minZ; $z <= $maxZ; ++$z) {
                for ($y = $minY; $y <= $maxY; ++$y) {
                    $iterator->moveTo($x, $y, $z);
                    $iterator->currentSubChunk->setFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $fullId);
                }
            }
        }

        if ($world instanceof World) {
            Utils::updateChunks($world, $minX >> 4, $maxX >> 4, $minZ >> 4, $maxZ >> 4);
        }

        $time = microtime(true) - $time;
        if ($callable !== null) {
            $callable($time, (1 + $maxX - $minX) * (1 + $maxY - $minY) * (1 + $maxZ - $minZ));
        }
    }

    public function replace(ChunkManager $world, BlockToBlockMapping $mapping, ?callable $callable = null) : void
    {
        $time = microtime(true);

        $minX = $this->pos1->x;
        $maxX = $this->pos2->x;
        $minY = $this->pos1->y;
        $maxY = $this->pos2->y;
        $minZ = $this->pos1->z;
        $maxZ = $this->pos2->z;

        $mapping = $mapping->toFullBlock();
        $iterator = new BlockIterator($world);

        for ($x = $minX; $x <= $maxX; ++$x) {
            for ($z = $minZ; $z <= $maxZ; ++$z) {
                for ($y = $minY; $y <= $maxY; ++$y) {
                    $iterator->moveTo($x, $y, $z);
                    if (isset($mapping[$fullBlock = $iterator->currentSubChunk->getFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f)])) {
                        $iterator->currentSubChunk->setFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $mapping[$fullBlock]);
                    }
                }
            }
        }

        if ($world instanceof World) {
            Utils::updateChunks($world, $minX >> 4, $maxX >> 4, $minZ >> 4, $maxZ >> 4);
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
