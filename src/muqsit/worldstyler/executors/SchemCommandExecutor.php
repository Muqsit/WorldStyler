<?php

declare(strict_types=1);
namespace muqsit\worldstyler\executors;

use muqsit\worldstyler\schematics\Schematic;
use muqsit\worldstyler\utils\Utils;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class SchemCommandExecutor extends BaseCommandExecutor {

    protected function initExecutor() : void
    {
        $this->addOption("async", self::OPT_OPTIONAL, true);
    }

    public function onCommandExecute(CommandSender $sender, Command $command, string $label, array $args, array $opts) : bool
    {
        if (!isset($args[0]) || ($args[0] !== 'list' && $args[0] !== 'paste') || ($args[0] === 'paste' && !isset($args[1]))) {
            $sender->sendMessage(TF::RED . '//schem list');
            $sender->sendMessage(TF::RED . '//schem paste <schematicname>');
            return false;
        }

        if ($args[0] === 'list') {
            $dir = $this->plugin->getDataFolder() . 'schematics/';
            if (!is_dir($dir)) {
                $sender->sendMessage(TF::RED . 'Directory ' . $dir . ' does not exist.');
                return false;
            }

            $files = 0;
            $sender->sendMessage(TF::YELLOW . 'Schematics:');
            foreach (scandir($dir) as $file) {
                $expl = explode(".", $file, 2);
                if (count($expl) === 2 && $expl[1] === 'schematic') {
                    $sender->sendMessage(TF::GREEN . ++$files . '. ' . $expl[0] . TF::GRAY . ' (' . Utils::humanFilesize($dir . $file) . ')');
                }
            }
            $sender->sendMessage(TF::ITALIC . TF::GRAY . 'Found ' . $files . ' schematics!');
            return true;
        }

        if ($args[0] === 'paste') {
            if(!$sender instanceof Player){
                $sender->sendMessage(TF::RED . "You cannot run this command.");
                return false;
            }
            $file = $this->plugin->getDataFolder() . 'schematics/' . $args[1] . '.schematic';
            if (!is_file($file)) {
                $sender->sendMessage(TF::RED . 'File "' . $file . '" not found.');
                return false;
            }

            $schematic = new Schematic($file);
            $force_async = $opts["async"] ?? null;
            $is_async = $force_async !== null ? ($force_async = $this->getBool((string)$force_async)) : $this->plugin->getConfig()->get("use-async-tasks", false);

            if ($force_async !== null) {
                $sender->sendMessage(TF::GRAY . 'Asynchronous /' . $label . ' ' . ($force_async ? 'enabled' : 'disabled'));
            }

            if ($is_async) {
                $schematic = $schematic->async();
            } else {
                $schematic->load();
            }

            $schematic->paste(
                $sender->getWorld(),
                $sender->getPosition(),
                true,
                function (float $time, int $changed) use ($sender) : void {
                    $sender->sendMessage(TF::GREEN . 'Took ' . number_format($time, 10) . 's to paste ' . number_format($changed) . ' blocks.');
                }
            );

            if (!$is_async) {
                $schematic->invalidate();
            }
        }
        return true;
    }
}