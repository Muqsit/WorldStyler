<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use muqsit\worldstyler\shapes\CommonShape;

use pocketmine\level\Level;

abstract class AsyncCommonShapeTask extends AsyncChunksChangeTask {

    /** @var string */
    private $common_shape;

    public function __construct(CommonShape $common_shape, Level $level, array $chunks, ?callable $callable = null)
    {
        $this->common_shape = serialize($common_shape);

        $this->setLevel($level);
        $this->setChunks($chunks);
        $this->setCallable($callable);
    }

    protected function getCommonShape() : CommonShape
    {
        return unserialize($this->common_shape);
    }
}