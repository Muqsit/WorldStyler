<?php

declare(strict_types=1);
namespace muqsit\worldstyler\shapes\async\tasks;

use muqsit\worldstyler\Selection;
use pocketmine\math\Vector3;
use pocketmine\Server;

class AsyncCuboidCopyTask extends AsyncCuboidTask {

    protected $set_chunks = false;

    /** @var string */
    private $clipboard;

    /** @var string */
    private $clipboard_caps;

    /** @var Vector3 */
    private $relative_pos;

    /** @var int */
    private $selectionId;

    public function setRelativePos(Vector3 $relative_pos) : void
    {
        $this->relative_pos = $relative_pos;
    }

    public function onRun() : void
    {
        $world = $this->getChunkManager();
        $cuboid = $this->getCuboid();

        $cuboid->copy($world, $this->relative_pos, [$this, "updateStatistics"]);
        $this->saveChunks($world, $cuboid->pos1, $cuboid->pos2);

        $this->clipboard = self::serialize($cuboid->selection->getClipboard());
        $this->relative_pos = $cuboid->selection->getClipboardRelativePos();
        $this->clipboard_caps = self::serialize($cuboid->selection->getClipboardCaps());
        $this->selectionId = $cuboid->selection->getId();
    }

    public function onCompletion() : void
    {
        parent::onCompletion();

        /** @var Selection $selection */
        $selection = Server::getInstance()->getPluginManager()->getPlugin("WorldStyler")->getSelection($this->selectionId);
        if ($selection !== null) {
            $selection->setClipboard(self::unserialize($this->clipboard), $this->relative_pos, self::unserialize($this->clipboard_caps));
        }
    }
}