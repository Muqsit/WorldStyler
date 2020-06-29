<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use pocketmine\math\Vector3;

class AsyncCommonShapeStackTask extends AsyncCommonShapeTask {

    /** @var Vector3 */
    private $start;

    /** @var Vector3 */
    private $increase;

    /** @var int */
    private $repetitions;

    /** @var bool */
    private $replace_air;

    public function startFrom(Vector3 $pos) : void
    {
        $this->start = $pos;
    }

    public function increaseBy(Vector3 $pos) : void
    {
        $this->increase = $pos;
    }

    public function repeat(int $repetitions) : void
    {
        $this->repetitions = $repetitions;
    }

    public function replaceAir(bool $replace_air) : void
    {
        $this->replace_air = $replace_air;
    }

    public function onRun() : void
    {
        $world = $this->getChunkManager();
        $common_shape = $this->getCommonShape();
        $common_shape->stack($world, $this->start, $this->increase, $this->repetitions, $this->replace_air, [$this, "updateStatistics"]);

        $caps = $common_shape->selection->getClipboardCaps();
        $min_pos = $this->start->add($this->increase->x * $caps->x, 0, $this->increase->z * $caps->z);
        $max_pos = $min_pos->multiply($this->repetitions);
        $this->saveChunks($world, $min_pos, $max_pos);
    }
}