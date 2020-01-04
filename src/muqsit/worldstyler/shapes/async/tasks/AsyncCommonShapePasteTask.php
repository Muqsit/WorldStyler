<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use pocketmine\math\Vector3;

class AsyncCommonShapePasteTask extends AsyncCommonShapeTask {

    /** @var Vector3 */
    private $relative_pos;

    /** @var bool */
    private $replace_air;

    public function setRelativePos(Vector3 $relative_pos) : void
    {
        $this->relative_pos = $relative_pos;
    }

    public function replaceAir(bool $replace_air) : void
    {
        $this->replace_air = $replace_air;
    }

    public function onRun() : void
    {
        $level = $this->getChunkManager();
        $common_shape = $this->getCommonShape();
        $common_shape->paste($level, $this->relative_pos, $this->replace_air, [$this, "updateStatistics"]);

        $caps = $common_shape->selection->getClipboardCaps();
        $min_pos = $this->relative_pos;
        $max_pos = $this->relative_pos->add($caps);
        $this->saveChunks($level, $min_pos, $max_pos);
    }
}