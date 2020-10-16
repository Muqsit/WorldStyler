<?php

declare(strict_types=1);
namespace muqsit\worldstyler;

use muqsit\worldstyler\executors\CopyCommandExecutor;
use muqsit\worldstyler\executors\FixPCBlocksExecutor;
use muqsit\worldstyler\executors\PasteCommandExecutor;
use muqsit\worldstyler\executors\PosCommandExecutor;
use muqsit\worldstyler\executors\ReplaceCommandExecutor;
use muqsit\worldstyler\executors\SchemCommandExecutor;
use muqsit\worldstyler\executors\SetCommandExecutor;
use muqsit\worldstyler\executors\StackCommandExecutor;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class WorldStyler extends PluginBase {

    /** @var Selection[] */
    private $selections = [];

    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);

        if (!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }

        if (!is_dir($this->getDataFolder() . 'schematics/')) {
            mkdir($this->getDataFolder() . 'schematics/');
        }

        $this->saveResource("config.yml");

        $commands = $this->getServer()->getCommandMap();
        $commands->getCommand("/copy")->setExecutor(new CopyCommandExecutor($this));
        $commands->getCommand("/fixpcblocks")->setExecutor(new FixPCBlocksExecutor($this));
        $commands->getCommand("/paste")->setExecutor(new PasteCommandExecutor($this));
        $commands->getCommand("/pos1")->setExecutor(new PosCommandExecutor($this, 1));
        $commands->getCommand("/pos2")->setExecutor(new PosCommandExecutor($this, 2));
        $commands->getCommand("/replace")->setExecutor(new ReplaceCommandExecutor($this));
        $commands->getCommand("/schem")->setExecutor(new SchemCommandExecutor($this));
        $commands->getCommand("/set")->setExecutor(new SetCommandExecutor($this));
        $commands->getCommand("/stack")->setExecutor(new StackCommandExecutor($this));
    }

    public function getPlayerSelection(Player $player) : ?Selection
    {
        return $this->getSelection($player->getId());
    }

    public function getSelection(int $pid) : ?Selection
    {
        return $this->selections[$pid] ?? ($this->selections[$pid] = new Selection($pid));
    }

    public function removeSelection(int $pid) : void
    {
        unset($this->selections[$pid]);
    }
}
