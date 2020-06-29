<?php

declare(strict_types=1);
namespace muqsit\worldstyler\schematics;

use muqsit\worldstyler\schematics\async\AsyncSchematic;
use pocketmine\block\BlockFactory;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use Prison\level\BlockIterator;
use Prison\level\LevelUtils;

class Schematic {

    /** @var string */
    protected $file;

    /** @var CompoundTag */
    protected $namedtag;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function load() : void
    {
        $this->namedtag = (new BigEndianNbtSerializer())->readCompressed(file_get_contents($this->file))->getTag();
    }

    public function getWidth() : int
    {
        return $this->namedtag->getShort("Width");
    }

    public function getLength() : int
    {
        return $this->namedtag->getShort("Length");
    }

    public function getHeight() : int
    {
        return $this->namedtag->getShort("Height");
    }

    public function paste(ChunkManager $world, Vector3 $relative_pos, ?callable $callable = null) : void
    {
        $time = microtime(true);

        $blockIds = $this->namedtag->getByteArray("Blocks");
        $blockDatas = $this->namedtag->getByteArray("Data");

        $width = $this->getWidth();
        $length = $this->getLength();
        $height = $this->getHeight();

        $relative_pos = $relative_pos->floor();
        $relx = $relative_pos->x;
        $rely = $relative_pos->y;
        $relz = $relative_pos->z;

        $iterator = new BlockIterator($world);

        $wl = $width * $length;

        for ($x = 0; $x < $width; ++$x) {
            $xPos = $x + $relx;

            for ($z = 0; $z < $length; ++$z) {
                $zPos = $z + $relz;
                $zwx = $z * $width + $x;

                for ($y = 0; $y < $height; ++$y) {
                    $index = $y * $wl + $zwx;

                    $yPos = $y + $rely;
                    $iterator->moveTo($xPos, $yPos, $zPos);
                    $iterator->currentSubChunk->setFullBlock($xPos & 0x0f, $yPos & 0x0f, $zPos & 0x0f, BlockFactory::getInstance()->get(ord($blockIds{$index}), ord($blockDatas{$index}))->getFullId());
                }
            }
        }

        if ($world instanceof World) {
            LevelUtils::updateChunks($world, $relx >> 4, ($relx + $width - 1) >> 4, $relz >> 4, ($relz + $length - 1) >> 4);
        }

        $time = microtime(true) - $time;
        if ($callable !== null) {
            $callable($time, $width * $length * $height);
        }
    }

    public function invalidate() : void
    {
        $this->namedtag = null;
    }

    public function async() : AsyncSchematic
    {
        return new AsyncSchematic($this->file);
    }
}
