<?php

declare(strict_types=1);
namespace muqsit\worldstyler;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class EventHandler implements Listener {

    /** @var WorldStyler */
    private $plugin;

    public function __construct(WorldStyler $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPlayerQuit(PlayerQuitEvent $event) : void
    {
        $this->plugin->removeSelection($event->getPlayer()->getId());
    }
}
