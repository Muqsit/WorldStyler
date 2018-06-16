<?php

declare(strict_types=1);
namespace muqsit\worldstyler\schematics;

use muqsit\worldstyler\utils\BlockIterator;
use muqsit\worldstyler\utils\Utils;

use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNBTStream;

class Schematic {

    /** @var string */
    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function paste(ChunkManager $level, Vector3 $relative_pos, bool $replace_pc_blocks = true, &$time = null) : int
    {
        $time = microtime(true);

        $reader = new BigEndianNBTStream();
        $data = $reader->readCompressed(file_get_contents($this->file));

        $blockIds = $data->getByteArray("Blocks");
        $blockDatas = $data->getByteArray("Data");

        $width = $data->getShort("Width");
        $length = $data->getShort("Length");
        $height = $data->getShort("Height");

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
            }
        }

        if ($level instanceof Level) {
            Utils::updateChunks($level, $relx >> 4, ($relx + $width - 1) >> 4, $relz >> 4, ($relz + $length - 1) >> 4);
        }

        $time = microtime(true) - $time;
        return $width * $length * $height;
    }
}