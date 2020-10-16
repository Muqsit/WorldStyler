<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes;

use muqsit\worldstyler\shapes\async\AsyncCommonShape;
use muqsit\worldstyler\Selection;
use muqsit\worldstyler\utils\BlockIterator;
use muqsit\worldstyler\utils\Utils;

use pocketmine\block\BlockLegacyIds;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use pocketmine\math\Vector3;

class CommonShape {

    public static function fromSelection(Selection $selection) : CommonShape
    {
        return new CommonShape($selection);
    }

    /** @var Selection */
    public $selection;

    public function __construct(Selection $selection)
    {
        $this->selection = $selection;
    }

    public function stack(ChunkManager $world, Vector3 $start, Vector3 $increase, int $repetitions, bool $replace_air = true, ?callable $callable = null) : void
    {
        $totalTime = 0;
        $changed = 0;

        $caps = $this->selection->getClipboardCaps();
        $xCap = $caps->x;
        $yCap = $caps->y;
        $zCap = $caps->z;

        $xIncrease = $increase->x;
        $yIncrease = $increase->y;
        $zIncrease = $increase->z;

        $paste_callable = function (float $timeTaken, int $blocksChanged) use(&$totalTime, &$changed) : void {
            $totalTime += $timeTaken;
            $changed += $blocksChanged;
        };

        while (--$repetitions >= 0) {
            $this->paste($world, $start, $replace_air, $paste_callable);

            $start->x += $xIncrease * $xCap;
            $start->y += $yIncrease * $yCap;
            $start->z += $zIncrease * $zCap;
        }

        if ($callable !== null) {
            $callable($totalTime, $changed);
        }
    }

    public function paste(ChunkManager $world, Vector3 $relative_pos, bool $replace_air = true, ?callable $callable = null) : void
    {
        $changed = 0;
        $time = microtime(true);

        $relative_pos = $relative_pos->floor()->addVector($this->selection->getClipboardRelativePos());
        $relx = $relative_pos->x;
        $rely = $relative_pos->y;
        $relz = $relative_pos->z;

        $clipboard = $this->selection->getClipboard();

        $caps = $this->selection->getClipboardCaps();
        $xCap = $caps->x;
        $yCap = $caps->y;
        $zCap = $caps->z;

        $iterator = new BlockIterator($world);

        for ($x = 0; $x <= $xCap; ++$x) {
            $xPos = $relx + $x;
            for ($z = 0; $z <= $zCap; ++$z) {
                $zPos = $relz + $z;
                for ($y = 0; $y <= $yCap; ++$y) {
                    $fullBlock = $clipboard[World::blockHash($x, $y, $z)] ?? null;
                    if ($fullBlock !== null) {
                        if ($replace_air || ($fullBlock >> 4) !== BlockLegacyIds::AIR) {
                            $yPos = $rely + $y;
                            $iterator->moveTo($xPos, $yPos, $zPos);
                            $iterator->currentSubChunk->setFullBlock($xPos & 0x0f, $yPos & 0x0f, $zPos & 0x0f, (int)$fullBlock);
                            ++$changed;
                        }
                    }
                }
            }
        }

        if ($world instanceof World) {
            Utils::updateChunks($world, $relx >> 4, ($relx + $xCap) >> 4, $relz >> 4, ($relz + $zCap) >> 4);
        }

        $time = microtime(true) - $time;
        if ($callable !== null) {
            $callable($time, $changed);
        }
    }

    public function async() : AsyncCommonShape
    {
        return new AsyncCommonShape($this->selection);
    }
}
