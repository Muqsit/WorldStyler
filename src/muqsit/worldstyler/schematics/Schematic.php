<?php

declare(strict_types=1);
namespace muqsit\worldstyler\schematics;

use muqsit\worldstyler\schematics\async\AsyncSchematic;
use muqsit\worldstyler\utils\BlockIterator;
use muqsit\worldstyler\utils\Utils;

use wumpotamus\chunkloader\ChunkRegion;

use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;

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
        $this->namedtag = (new BigEndianNBTStream())->readCompressed(file_get_contents($this->file));
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

    public function paste(ChunkManager $level, Vector3 $relative_pos, bool $replace_pc_blocks = true, ?callable $callable = null) : void
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

        $iterator = new BlockIterator($level);

        $wl = $width * $length;

        for ($x = 0; $x < $width; ++$x) {
            $xPos = $x + $relx;

            for ($z = 0; $z < $length; ++$z) {
                $zPos = $z + $relz;
                $zwx = $z * $width + $x;
				
				ChunkRegion::onChunkGenerated($level, $xPos >> 4, $zPos >> 4, function() use($rely, $replace_pc_blocks, $iterator, $xPos, $zPos, $height, $wl, $zwx, $blockIds, $blockDatas){
					for ($y = 0; $y < $height; ++$y) {
						$index = $y * $wl + $zwx;

						$id = ord($blockIds{$index});
						$damage = ord($blockDatas{$index});

						if ($replace_pc_blocks && isset(Utils::REPLACEMENTS[$id])) {
							[$new_id, $new_damage] = Utils::REPLACEMENTS[$id][$damage] ?? Utils::REPLACEMENTS[$id][-1] ?? [$id, $damage];
							$id = $new_id ?? $id;
							$damage = $new_damage ?? $damage;
						}
					
						$yPos = $y + $rely;
						$iterator->moveTo($xPos, $yPos, $zPos);
						$iterator->currentSubChunk->setBlock($xPos & 0x0f, $yPos & 0x0f, $zPos & 0x0f, $id, $damage);
					}
				});
				$level->unloadChunk( $xPos >> 4, $zPos >> 4);
            }
        }

        if ($level instanceof Level) {
            Utils::updateChunks($level, $relx >> 4, ($relx + $width - 1) >> 4, $relz >> 4, ($relz + $length - 1) >> 4);
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