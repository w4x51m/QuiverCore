<?php

namespace Quiver\ChatFilter;

use pocketmine\utils\TextFormat;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\scheduler\Task;
use pocketmine\utils\Utils;
use pocketmine\Player;
use Quiver\Core\QuiverCore;

/**
 * Clears the recent chat messages
 */
class ChatFilterTask extends Task {

  private $plugin;
    
    public function __construct(QuiverCore $plugin) {
        $this->plugin = $plugin;
        return;
    }

	
    /**
     * When the task is ran
     *
     * @param  integer $currentTick The current tick
     * @return null                 Nothing
     */
    public function onRun($currentTick) {
        $this->plugin->filter->clearRecentChat();
    }
}