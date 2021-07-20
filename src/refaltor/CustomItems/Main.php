<?php

namespace refaltor\CustomItems;

use pocketmine\plugin\PluginBase;
use refaltor\CustomItems\Events\OnData;
use refaltor\CustomItems\Loader\Load;

class Main extends PluginBase
{
    /** @var self */
    private static $instance;

    public static function getInstance(): self{
        return self::$instance;
    }

    public function onEnable()
    {
        self::$instance = $this;
        $this->saveDefaultConfig();
        Load::register();
        $this->getServer()->getPluginManager()->registerEvents(new OnData(), $this);
    }
}