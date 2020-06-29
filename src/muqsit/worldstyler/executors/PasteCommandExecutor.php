<?php

declare(strict_types=1);
namespace muqsit\worldstyler\executors;

use muqsit\worldstyler\shapes\CommonShape;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class PasteCommandExecutor extends BaseCommandExecutor {

    protected function initExecutor() : void
    {
        $this->addOption("async", self::OPT_OPTIONAL, true);
    }

    public function onCommandExecute(CommandSender $sender, Command $command, string $label, array $args, array $opts) : bool
    {
        if(!$sender instanceof Player){
            $sender->sendMessage(TF::RED . "You cannot run this command.");
            return false;
        }
        $selection = $this->plugin->getPlayerSelection($sender);

        if (!$selection->hasClipboard()) {
            $sender->sendMessage(TF::RED . 'You have copied nothing into your clipboard.');
            return false;
        }

        $air = !(isset($args[0]) && $args[0] === "noair");

        $common_shape = CommonShape::fromSelection($selection);
        $force_async = $opts["async"] ?? null;
        if ($force_async !== null ? ($force_async = $this->getBool((string)$force_async)) : $this->plugin->getConfig()->get("use-async-tasks", false)) {
            $cuboid = $common_shape->async();
        }

        if ($force_async !== null) {
            $sender->sendMessage(TF::GRAY . 'Asynchronous /' . $label . ' ' . ($force_async ? 'enabled' : 'disabled'));
        }

        $common_shape->paste(
            $sender->getWorld(),
            $sender->getPosition(),
            $air,
            function (float $time, int $changed) use ($sender, $air) : void {
                $sender->sendMessage(TF::GREEN . 'Pasted ' . number_format($changed) . ' blocks in ' . number_format($time, 10) . 's from your clipboard' . ($air ? null : ' (no-air)') . '.');
            }
        );
        return true;
    }
}