<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use muqsit\worldstyler\shapes\CommonShape;

use pocketmine\world\World;

abstract class AsyncCommonShapeTask extends AsyncChunksChangeTask {

    /** @var string */
    private $common_shape;

    public function __construct(CommonShape $common_shape, World $world, array $chunks, ?callable $callable = null)
    {
        $this->common_shape = serialize($common_shape);

        $this->setWorld($world);
        $this->setChunks($chunks);
        $this->setCallable($callable);
    }

    protected function getCommonShape() : CommonShape
    {
        return unserialize($this->common_shape);
    }
}