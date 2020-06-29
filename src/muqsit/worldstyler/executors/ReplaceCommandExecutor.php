<?php

declare(strict_types=1);
namespace muqsit\worldstyler\executors;

use muqsit\worldstyler\shapes\Cuboid;
use muqsit\worldstyler\utils\BlockToBlockMapping;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as TF;
use Prison\level\LevelUtils;

class ReplaceCommandExecutor extends BaseCommandExecutor {

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
        $selection = $this->plugin->getPlayerSelection($sender);

        $count = $selection->getPositionCount();
        if ($count < 2) {
            $sender->sendMessage(TF::RED . 'You have not selected enough vertices.');
            return false;
        }

        if ((count($args) & 1) === 1) {
            $sender->sendMessage(TF::RED . '//replace ...<blockToReplace> <replacementBlock>');
            return false;
        }

        $mapping = new BlockToBlockMapping();
        foreach (array_chunk($args, 2) as $pair) {
            foreach ($pair as &$block) {
                $block = LevelUtils::getBlockFromString($block);
                if ($block === null) {
                    $sender->sendMessage(TF::RED . 'Invalid block ' . $block . ' given.');
                    return false;
                }
            }

            $mapping->add($pair[0], $pair[1]);
        }

        $cuboid = Cuboid::fromSelection($selection);
        $force_async = $opts["async"] ?? null;
        if ($force_async !== null ? ($force_async = $this->getBool((string)$force_async)) : $this->plugin->getConfig()->get("use-async-tasks", false)) {
            $cuboid = $cuboid->async();
        }

        if ($force_async !== null) {
            $sender->sendMessage(TF::GRAY . 'Asynchronous /' . $label . ' ' . ($force_async ? 'enabled' : 'disabled'));
        }

        $cuboid->replace(
            $sender->getWorld(),
            $mapping,
            function (float $time, int $changed) use ($sender) : void {
                $sender->sendMessage(TF::GREEN . 'Replaced ' . number_format($changed) . ' blocks in ' . number_format($time, 10) . 's');
            }
        );
        return true;
    }
}