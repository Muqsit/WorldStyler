<?php

declare(strict_types=1);
namespace muqsit\worldstyler\executors;

use muqsit\worldstyler\WorldStyler;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class PosCommandExecutor extends BaseCommandExecutor {

    /** @var int */
    private $position_index;

    public function __construct(WorldStyler $plugin, int $position_index)
    {
        parent::__construct($plugin);
        $this->position_index = $position_index;
    }

    public function onCommandExecute(CommandSender $sender, Command $command, string $label, array $args, array $opts) : bool
    {
        if(!$sender instanceof Player){
            $sender->sendMessage(TF::RED . "You cannot run this command.");
            return false;
        }
        $pos = $sender->getPosition();
        $this->plugin->getPlayerSelection($sender)->setPosition($this->position_index, $pos);
        $sender->sendMessage(TF::GREEN . 'Selected position #' . $this->position_index . ' as X=' . $pos->getFloorX() . ', Y=' . $pos->getFloorY() . ', Z=' . $pos->getFloorZ());
        return true;
    }
}