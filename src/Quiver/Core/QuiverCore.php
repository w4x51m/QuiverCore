<?php

namespace Quiver\Core;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

//chat filter
use Quiver\ChatFilter\ChatFilter;
use Quiver\ChatFilter\ChatFilterTask;

class QuiverCore extends PluginBase implements Listener{
	
	public $users = [];
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("QuiverCore enabled! Enjoy!");
		
		$this->filter = new ChatFilter();
        $this->getScheduler()->scheduleRepeatingTask(new ChatFilterTask($this), 30);
		
		if($this->getConfig()->get('enabled') == false) {
            $this->setEnabled(false);
			$this->getLogger()->info(TextFormat::RED . "PLUGIN HAS BEEN DISABLED IN THE CONFIG");
            return;
        }
	}
	
		public function onPlayerChat(PlayerChatEvent $event) {
        if (array($event->getPlayer()->getDisplayName(), $this->users) && !$this->filter->check($event->getPlayer(), $event->getMessage())) {
            $event->setCancelled(true);
          //  $event->getPlayer()->sendMessage(TextFormat::RED . "COSMETICS> I'm sorry, I can't let you say that.");
        }
    }
}