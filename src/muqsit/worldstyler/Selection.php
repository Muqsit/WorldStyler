<?php

declare(strict_types=1);
namespace muqsit\worldstyler;

use pocketmine\math\Vector3;

class Selection {

    /** @var Vector3[] */
    private $positions = [];

    /** @var int[] */
    private $clipboard;

    /** @var Vector3 */
    private $clipboard_relative_pos;

    /** @var Vector3 */
    private $clipboard_caps;

    /** @var int */
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setClipboard(array $blockArray, Vector3 $relative_pos, Vector3 $caps) : void
    {
        $this->clipboard = empty($blockArray) ? null : $blockArray;
        $this->clipboard_relative_pos = $relative_pos;
        $this->clipboard_caps = $caps;
    }

    public function getClipboard() : ?array
    {
        return $this->clipboard;
    }

    public function getClipboardRelativePos() : ?Vector3
    {
        return $this->clipboard_relative_pos;
    }

    public function getClipboardCaps() : ?Vector3
    {
        return $this->clipboard_caps;
    }

    public function hasClipboard() : bool
    {
        return $this->clipboard !== null;
    }

    public function setPosition(int $index, Vector3 $pos) : void
    {
        $this->positions[$index] = $pos->floor();
    }

    public function reset() : void
    {
        $this->resetPositions();
    }

    public function resetPositions() : void
    {
        $this->positions = [];
    }

    public function getPosition(int $index) : ?Vector3
    {
        return $this->positions[$index] ?? null;
    }

    public function removePosition(int $index) : void
    {
        unset($this->positions[$index]);
    }

    public function getPositionCount() : int
    {
        return count($this->positions);
    }
}
