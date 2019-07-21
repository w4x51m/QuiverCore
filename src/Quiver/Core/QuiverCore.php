<?php

declare(strict_types=1);

namespace Quiver\Core;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
//Bot
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\utils\TextFormat;
use Quiver\Bot\BotEntity;
use Quiver\Bot\NPCTask;

//chat filter
use Quiver\ChatFilter\ChatFilter;
use Quiver\ChatFilter\ChatFilterTask;
//Alerts
use Quiver\Alerts\AlertTask;

class QuiverCore extends PluginBase implements Listener{

	public $users = [];

	public function onEnable(){
		
		self::$instance = $this;
		Entity::registerEntity(BotEntity::class, true);
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

	 	$this->getLogger()->info("QuiverCore enabled! Enjoy!");

    $this->filter = new ChatFilter();
    $this->getScheduler()->scheduleRepeatingTask(new ChatFilterTask($this), 30);

		$this->getScheduler()->scheduleRepeatingTask(new AlertTask($this), 2000);

		if($this->getConfig()->get('enabled') == false) {
            $this->setEnabled(false);
			$this->getLogger()->info(TextFormat::RED . "PLUGIN HAS BEEN DISABLED IN THE CONFIG");
            return;
        }
	}
	
	public static function getInstance() : self{
		return self::$instance;
	}
	
	public function onEntitySpawn(EntitySpawnEvent $event) : void{
		$entity = $event->getEntity();
		if($entity instanceof BotEntity) $this->getScheduler()->scheduleDelayedTask(new NPCTask("start", $entity), 20);
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(strtolower($command->getName()) === "bot"){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED . "You must be a player to use this command");
				return false;
			}
			if(!$sender->isOp()){
				$sender->sendMessage(TextFormat::RED . "You must be opped to spawn bot.");
				return false;
			}
			if(count($args) < 1){
				$sender->sendMessage(TextFormat::GRAY . "Usage: /bot <name>");
				return false;
			}
			$name = implode(" ", $args);
			$nbt = Entity::createBaseNBT($sender, null, 2, 2);
			$nbt->setTag($sender->namedtag->getTag("Skin"));
			$npc = new BotEntity($sender->getLevel(), $nbt);
			$npc->setNameTag($name);
			$npc->setNameTagAlwaysVisible(true);
			$npc->spawnToAll();
			$sender->sendMessage(TextFormat::GREEN . "Spawned " . $name);
		}
		return true;
	}
	
	public function onMove(PlayerMoveEvent $event) : void{
		$player = $event->getPlayer();
		$from = $event->getFrom();
		$to = $event->getTo();
		if($this->getConfig()->get("spin") === "on"){
			if($from->distance($to) < 0.1) return;
			foreach($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy(10, 10, 10), $player) as $e){
				if($e instanceof BotEntity){
					$pk = new MoveActorAbsolutePacket();
					$v = new Vector2($e->x, $e->z);
					$pk->entityRuntimeId = $e->getId();
					$pk->position = $e->asVector3()->add(0, 1.5, 0);
					$pk->zRot = ((atan2($player->z - $e->z, $player->x - $e->x) * 180) / M_PI) - 90;
					$pk->yRot = ((atan2($player->z - $e->z, $player->x - $e->x) * 180) / M_PI) - 90;
					$pk->xRot = ((atan2($v->distance($player->x, $player->z), $player->y - $e->y) * 180) / M_PI) - 90;
					$player->dataPacket($pk);
					$e->setRotation(((atan2($player->z - $e->z, $player->x - $e->x) * 180) / M_PI) - 90, ((atan2($v->distance($player->x, $player->z), $player->y - $e->y) * 180) / M_PI) - 90);
				}
			}
		}
	}

		public function onPlayerChat(PlayerChatEvent $event) {
        if (array($event->getPlayer()->getDisplayName(), $this->users) && !$this->filter->check($event->getPlayer(), $event->getMessage())) {
            $event->setCancelled(true);
          //  $event->getPlayer()->sendMessage(TextFormat::RED . "COSMETICS> I'm sorry, I can't let you say that.");
        }
    }
}
