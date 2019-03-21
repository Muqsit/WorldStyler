<?php

declare(strict_types=1);
namespace muqsit\worldstyler\utils;

use pocketmine\math\Vector3;

class Clipboard {

    /** @var int[] */
    private $blocks;

    /** @var Vector3 */
    private $relative_pos;

    /** @var Vector3 */
    private $caps;

    public function __construct(array $blockHashes, Vector3 $relative_pos, Vector3 $caps)
    {
        $this->blocks = $blockHashes;
        $this->relative_pos = $relative_pos;
        $this->caps = $caps;
    }

    public function iterate(Vector3 $relative_pos, &$x = null, &$y = null, &$z = null) : int
    {
        $relx = $relative_pos->getFloorX() + $this->relative_pos->x;
        $rely = $relative_pos->getFloorY() + $this->relative_pos->y;
        $relz = $relative_pos->getFloorZ() + $this->relative_pos->z;

        $clipboard = $this->selection->getClipboard();

        $caps = $this->selection->getClipboardCaps();
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
                            $iterator->currentSubChunk->setFullBlock($xPos & 0x0f, $yPos & 0x0f, $zPos & 0x0f, $fullBlock);
                            ++$changed;
                        }
                    }
                }
            }
        }
    }
}