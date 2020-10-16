<?php

declare(strict_types=1);
namespace muqsit\worldstyler\executors;

use muqsit\worldstyler\WorldStyler;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

abstract class BaseCommandExecutor implements CommandExecutor {

    const OPT_REQUIRED = 0;
    const OPT_OPTIONAL = 1;
    const OPT_NO_VALUE = 2;

    /** @var WorldStyler */
    protected $plugin;

    /** @var int[] */
    protected $options = [];

    public function __construct(WorldStyler $plugin)
    {
        $this->plugin = $plugin;
        $this->initExecutor();
    }

    protected function initExecutor() : void
    {
    }

    public function addOption(string $option, int $type, bool $is_long) : void
    {
        $this->options[($is_long ? "--" : "-") . $option] = $type;
    }

    protected function parseOptions(array $args, &$indexes) : array
    {
        $options = [];
        $indexes = [];

        if (!empty($this->options)) {
            foreach ($args as $index => $arg) {
                if ($arg[0] === "-") {
                    $offset = 0;
                    $len = strlen($arg);
                    while ($offset < $len) {
                        if (isset($this->options[$opt = substr($arg, 0, $offset)])) {
                            $indexes[$index] = $index;
                            $value = true;
                            $type = $this->options[$opt];
                            if ($type !== self::OPT_NO_VALUE) {
                                $value = $offset === $len ? true : ltrim(substr($arg, $offset), "=");
                                if ($value === "") {
                                    $value = true;
                                }
                                if ($value === true && $type === self::OPT_REQUIRED) {
                                    throw new MissingOptionException("Value of flag '{$opt}' is mandatory");
                                }
                            }

                            $options[ltrim($opt, "-")] = $value;
                            break;
                        }
                        ++$offset;
                    }
                }
            }
        }

        return $options;
    }

    protected function getBool(string $option) : bool
    {
        return $option === "true" || $option === "1";
    }

    final public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        $opts = $this->parseOptions($args, $indexes);
        $args = array_values(array_diff_key($args, $indexes));
        return $this->onCommandExecute($sender, $command, $label, $args, $opts);
    }

    abstract public function onCommandExecute(CommandSender $sender, Command $command, string $label, array $args, array $opts) : bool;
}