<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes;

use muqsit\worldstyler\Selection;
use muqsit\worldstyler\utils\BlockIterator;
use muqsit\worldstyler\utils\Utils;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class ShapeUtils {

    public static function stack(ChunkManager $level, Selection $selection, Vector3 $start, Vector3 $increase, int $repetitions, bool $replace_air = true, &$totalTime = null) : ?int
    {
        $totalTime = 0;
        $blocks = 0;

        $caps = $selection->getClipboardCaps();
        $xCap = $caps->x;
        $yCap = $caps->y;
        $zCap = $caps->z;

        $xIncrease = $increase->x;
        $yIncrease = $increase->y;
        $zIncrease = $increase->z;

        while (--$repetitions >= 0) {
            $blocks += ShapeUtils::paste($level, $selection, $start, $replace_air, $time);
            $totalTime += $time;

            $start->x += $xIncrease * $xCap;
            $start->y += $yIncrease * $yCap;
            $start->z += $zIncrease * $zCap;
        }

        return $blocks;
    }

    public static function paste(ChunkManager $level, Selection $selection, Vector3 $relative_pos, bool $replace_air = true, &$time = null) : int
    {
        $changed = 0;
        $time = microtime(true);

        $relative_pos = $relative_pos->floor()->add($selection->getClipboardRelativePos());
        $relx = $relative_pos->x;
        $rely = $relative_pos->y;
        $relz = $relative_pos->z;

        $clipboard = $selection->getClipboard();

        $caps = $selection->getClipboardCaps();
        $xCap = $caps->x;
        $yCap = $caps->y;
        $zCap = $caps->z;

        $iterator = new BlockIterator($level);

        for ($x = 0; $x <= $xCap; ++$x) {
            $xPos = $relx + $x;
            for ($z = 0; $z <= $zCap; ++$z) {
                $zPos = $relz + $z;
                for ($y = 0; $y <= $yCap; ++$y) {
                    $fullBlock = $clipboard[Level::blockHash($x, $y, $z)] ?? null;
                    if ($fullBlock !== null) {
                        if ($replace_air || ($fullBlock >> 4) !== Block::AIR) {
                            $yPos = $rely + $y;
                            $iterator->moveTo($xPos, $yPos, $zPos);
                            $iterator->currentSubChunk->setBlock($xPos & 0x0f, $yPos & 0x0f, $zPos & 0x0f, $fullBlock >> 4, $fullBlock & 0xf);
                            ++$changed;
                        }
                    }
                }
            }
        }

        if ($level instanceof Level) {
            Utils::updateChunks($level, $relx >> 4, ($relx + $xCap) >> 4, $relz >> 4, ($relz + $zCap) >> 4);
        }

        $time = microtime(true) - $time;
        return $changed;
    }
}