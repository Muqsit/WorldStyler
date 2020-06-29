<?php

declare(strict_types=1);
namespace muqsit\worldstyler\executors;

use muqsit\worldstyler\shapes\Cuboid;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as TF;

class NiceStyle extends BaseCommandExecutor {

    protected function initExecutor() : void
    {
        $this->addOption("async", self::OPT_OPTIONAL, true);
    }

    public function onCommandExecute(CommandSender $sender, Command $command, string $label, array $args, array $opts) : bool
    {
        if(!$sender instanceof Player){
            $sender->sendMessage(TextFormat::RED . "This command is not for console.");
            return false;
        }
        $this->plugin->getPlayerSelection($sender)->setPosition(1, $sender->getPosition()->add(30, 30, 30));
        $this->plugin->getPlayerSelection($sender)->setPosition(2, $sender->getPosition()->subtract(30, 30, 30));
        $selection = $this->plugin->getPlayerSelection($sender);
        $count = $selection->getPositionCount();

        if ($count < 2) {
            $sender->sendMessage(TF::RED . 'You have not selected enough vertices.');
            return false;
        }

        $cuboid = Cuboid::fromSelection($selection);
        $force_async = $opts["async"] ?? null;
        if ($force_async !== null ? ($force_async = $this->getBool((string)$force_async)) : $this->plugin->getConfig()->get("use-async-tasks", false)) {
            $cuboid = $cuboid->async();
        }

        if ($force_async !== null) {
            $sender->sendMessage(TF::GRAY . 'Asynchronous /' . $label . ' ' . ($force_async ? 'enabled' : 'disabled'));
        }

        $cuboid->copy(
            $sender->getWorld(),
            $sender->getPosition()->asVector3()
        );
        $sender->sendMessage("Copied!");
        return true;
    }
}