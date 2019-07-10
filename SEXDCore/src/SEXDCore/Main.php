<?php
   
declare(strict_types=1);

namespace SEXDCore;

use pocketmine\plugin\PluginBase;
use pocketmine\level\Level;
use pocketmine\level\sound\{AnvilUseSound, AnvilFallSound, AnvilBreakSound, EndermanTeleportSound};
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\utils\{Config, TextFormat};
use pocketmine\block\{BlockToolType, Block, Lava, Water, Stair};
use pocketmine\{Server, Player};
use pocketmine\event\player\{PlayerMoveEvent, PlayerKickEvent, PlayerItemHeldEvent, PlayerInteractEvent, PlayerLoginEvent, PlayerDropItemEvent, PlayerQuitEvent, PlayerJoinEvent};
use pocketmine\item\Item;
use pocketmine\event\entity\{EntityDamageEvent, ExplosionPrimeEvent, EntityDamageByEntityEvent, EntityDamageByEntity};
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\command\{Command, ConsoleCommandSender, CommandSender};

use onebone\economyapi\EconomyAPI;
class Main extends PluginBase implements Listener{

	public function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->LoadAllLevels();			
        $this->getLogger()->info("SEXDCore監獄版本已經加載");
		}
    
	//加載世界
    public function LoadAllLevels() 
    {
        $level = $this->getServer()->getDefaultLevel();
        $path = $level->getFolderName();
        $p1 = dirname($path);
        $p2 = $p1."/worlds/";
        $dirnowfile = scandir($p2, 1);
        foreach ($dirnowfile as $dirfile)
        {
            if($dirfile != '.' && $dirfile != '..' && $dirfile != $path && is_dir($p2.$dirfile)) 
            {
                if (!$this->getServer()->isLevelLoaded($dirfile))
                {  //如果这个世界未加载
                    $this->getLogger()->info("正在加载世界：$dirfile");
                    //$this->getServer()->generateLevel($dirfile);//如果你的服务器是0.16以下的话，最好加上这句~
                    $this->getServer()->loadLevel($dirfile);
                    $level = $this->getServer()->getLevelbyName($dirfile);
                    if ($level->getName() != $dirfile) 
                    {  //温馨提示
                        $this->getLogger()->info("有張地圖怪怪的 請檢察");
                    }
                }
            }
        }
    }	
	
	
	//指令部分~
	    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
		switch ($command->getName()) {
			
               case "hub":
 			   $sender->teleport(Server::getInstance()->getDefaultLevel()->getSpawnLocation());
               $sender->sendMessage("§e已傳送~");
               return true;
			   break;

			   case "keys":
				if (isset($args[0]) == false){										
				   return true;
				   break;
			}
			    switch($args[0]){
			   
               case "1": 
               $pm = EconomyAPI::getInstance()->myMoney($sender);
			   $name = $sender->getName();
               $my = $this->getServer()->getPluginManager()->getPlugin("MysteryCrate");	
			   $a = "common";
			   if ($pm < 10000){
			   $sender->addTitle("餘額不足");
               return true;
			   }else{
               EconomyAPI::getInstance()->reduceMoney($sender, 10000);						   
               $my->giveKey($sender, $a, 1);
               $sender->getLevel()->addSound(new AnvilUseSound($sender->getLocation())); 
               $sender->addTitle("購買成功");
			   return true;  
			  }
			   
               case "2": 
               $pm = EconomyAPI::getInstance()->myMoney($sender);
			   $name = $sender->getName();
               $my = $this->getServer()->getPluginManager()->getPlugin("MysteryCrate");
               $b = "uncommon";			   
			   if ($pm < 50000){
			   $sender->addTitle("餘額不足");
               return true;
			   }else{
               EconomyAPI::getInstance()->reduceMoney($sender, 50000);
               $my->giveKey($sender, $b, 1);			   
               $sender->getLevel()->addSound(new AnvilUseSound($sender->getLocation()));				   
               $sender->addTitle("購買成功");
			   return true;
			   }
			   
               case "3": 
               $pm = EconomyAPI::getInstance()->myMoney($sender);
			   $name = $sender->getName();
               $my = $this->getServer()->getPluginManager()->getPlugin("MysteryCrate");
               $c = "mythic";			   
			   if ($pm < 100000){
			   $sender->addTitle("餘額不足");
               return true;
			   }else{
               EconomyAPI::getInstance()->reduceMoney($sender, 100000);
               $my->giveKey($sender, $c, 1);			   
               $sender->getLevel()->addSound(new AnvilUseSound($sender->getLocation()));
               $sender->addTitle("購買成功");
			   return true;
			   }
               case "4": 
               $pm = EconomyAPI::getInstance()->myMoney($sender);
			   $name = $sender->getName();
               $my = $this->getServer()->getPluginManager()->getPlugin("MysteryCrate");	
               $d = "legendary";			   
			   if ($pm < 150000){
			   $sender->addTitle("餘額不足");
               return true;
			   }else{
               EconomyAPI::getInstance()->reduceMoney($sender, 150000);
               $my->giveKey($sender, $d, 1);			   
               $sender->getLevel()->addSound(new AnvilUseSound($sender->getLocation()));
               $sender->addTitle("購買成功");
               return true;
			   }					   
			   

          }
		       #return true; 
    } 
               return true; 	
 }
	//玩家手持id
        public function ItemHeld(PlayerItemHeldEvent $event)
        {
            $pl = $event->getPlayer();
            $id = $event->getItem()->getId();
            $da = $event->getItem()->getDamage();
            if($pl->isOp()){
		    $pl->sendPopup("§l§d[§aITEM §eID§d] §6> §b${id}:{$da}");
			}else{
		    $pl->sendPopup("§l§d[§aITEM §eID§d] §6> §b${id}:{$da}");		
		}
}
			
    //虛空拉回代碼
    public function onMove(PlayerMoveEvent $event) {
			
			if($event->getPlayer()->getY() < -5) {
				
			$event->getPlayer()->teleport($event->getPlayer()->getLevel()->getSafeSpawn());
			$event->getPlayer()->addTitle("已傳回出生點");
		}
	}
	
	public function onDamage(EntityDamageEvent $event) {
		if($event->getEntity() instanceof Player && $event->getEntity()->getY() < 0) {
			$event->setCancelled();
		}
	}
	
	//礦石收入背包
   public function onBreak(BlockBreakEvent $event) { 
		$block = $event->getBlock();
		$player = $event->getPlayer();
		$inventory = $player->getInventory();
		$bool = false; 
		
        if($event->getPlayer()->getGamemode() === 0){
            if($event->isCancelled() === false){			
                if($event->getPlayer()->getInventory()->getItemInHand()->getId() === 0){
                    if($event->getBlock()->getId() === 18){
                        $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                        $event->getPlayer()->getInventory()->addItem(Item::get($event->getBlock()->getId(), $event->getBlock()->getDamage(), 1));
                    } elseif($event->getBlock()->getId() === 32) {
                        $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                        $event->getPlayer()->getInventory()->addItem(Item::get($event->getBlock()->getId(), $event->getBlock()->getDamage(), 1));
                    }
                } elseif($event->getBlock()->getId() === 18) {
                    if(mt_rand(1, 20) === 1){
                        $event->getPlayer()->getInventory()->addItem(Item::get(Item::SAPLING, 0, 1));
                    } elseif(mt_rand(1, 200) === 1) {
                        $event->getPlayer()->getInventory()->addItem(Item::get(Item::APPLE, 0, 1));
                    }
                } elseif($event->getBlock()->getId() === 59) {
                    $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                    if($event->getBlock()->getDamage() >= 7){
                        $event->getPlayer()->getInventory()->addItem(Item::get(Item::WHEAT));
                        $event->getPlayer()->getInventory()->addItem(Item::get(Item::WHEAT_SEEDS, 0, mt_rand(0, 3)));
                    } else {
                        $event->getPlayer()->getInventory()->addItem(Item::get(Item::WHEAT_SEEDS));
                    }
                } elseif($event->getBlock()->getId() === 457) {
                    $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                    if($event->getBlock()->getDamage() >= 7){
                        $event->getPlayer()->getInventory()->addItem(Item::get(Item::BEETROOT));
                        $event->getPlayer()->getInventory()->addItem(Item::get(Item::BEETROOT_SEEDS, 0, mt_rand(0, 3)));
                    } else {
                        $event->getPlayer()->getInventory()->addItem(Item::get(Item::BEETROOT_SEEDS));
                    }
                } elseif($event->getBlock()->getId() === 105) {
                    $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                    $event->getPlayer()->getInventory()->addItem(Item::get(Item::MELON_SEEDS));
                } elseif($event->getBlock()->getId() === 104) {
                    $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                    $event->getPlayer()->getInventory()->addItem(Item::get(Item::PUMPKIN_SEEDS));
                } elseif($event->getBlock()->getId() === 142) {
                    $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                    $event->getPlayer()->getInventory()->addItem(Item::get(Item::POTATO, 0, $event->getBlock()->getDamage() >= 7 ? mt_rand(1, 4) : 1));
                } elseif($event->getBlock()->getId() === 141) {
                    $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                    $event->getPlayer()->getInventory()->addItem(Item::get(Item::CARROT, 0, $event->getBlock()->getDamage() >= 7 ? mt_rand(1, 4) : 1));
                } elseif($event->getBlock()->getId() === 32) {
                    $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                    $event->getPlayer()->getInventory()->addItem(Item::get(Item::STICK, 0, mt_rand(0, 2)));
                } elseif($event->getBlock()->getId() === 31) {
                    $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                    if(mt_rand(0, 14) === 0){
                        $event->getPlayer()->getInventory()->addItem(Item::get(Item::WHEAT_SEEDS));
                    }
                } elseif($event->getBlock()->getToolType() === BlockToolType::TYPE_PICKAXE) {
                    if($event->getPlayer()->getInventory()->getItemInHand()->getId() === 278 or $event->getPlayer()->getInventory()->getItemInHand()->getId() === 257 or $event->getPlayer()->getInventory()->getItemInHand()->getId() === 285 or $event->getPlayer()->getInventory()->getItemInHand()->getId() === 274 or $event->getPlayer()->getInventory()->getItemInHand()->getId() === 270){
                        $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                        if($event->getBlock()->getId() === 73){
						$event->getPlayer()->getInventory()->addItem(Item::get(Item::REDSTONE_DUST, 0, mt_rand(1, 3)));
							$event->getPlayer()->addXp($event->getXpDropAmount());
                            $event->setXpDropAmount(0);	
                        }else if($event->getBlock()->getId() === 74){
						$event->getPlayer()->getInventory()->addItem(Item::get(Item::REDSTONE_DUST	, 0, mt_rand(1, 3)));
							$event->getPlayer()->addXp($event->getXpDropAmount());
                            $event->setXpDropAmount(0);	
                        } elseif($event->getBlock()->getId() === 21) {
                            $event->getPlayer()->getInventory()->addItem(Item::get(Item::DYE, 4, mt_rand(1, 3)));	
							$event->getPlayer()->addXp($event->getXpDropAmount());
                            $event->setXpDropAmount(0);	
							} elseif($event->getBlock()->getId() === 16) {
                            $event->getPlayer()->getInventory()->addItem(Item::get(Item::COAL, 0, 1));
							$event->getPlayer()->addXp($event->getXpDropAmount());
                            $event->setXpDropAmount(0);	
							} elseif($event->getBlock()->getId() === 129) {
                            $event->getPlayer()->getInventory()->addItem(Item::get(Item::EMERALD, 0, 1));
							$event->getPlayer()->addXp($event->getXpDropAmount());
                            $event->setXpDropAmount(0);								
                        } elseif($event->getBlock()->getId() === 56) {
                            $event->getPlayer()->getInventory()->addItem(Item::get(Item::DIAMOND, 0, 1));
							$event->getPlayer()->addXp($event->getXpDropAmount());
                            $event->setXpDropAmount(0);	
							} elseif($event->getBlock()->getId() === 1) {
                            $event->getPlayer()->getInventory()->addItem(Item::get(Item::STONE, 0, 1));
                        } elseif($event->getBlock()->getId() === 153) {
							$event->getPlayer()->addXp($event->getXpDropAmount());
                            $event->setXpDropAmount(0);								
                            $event->getPlayer()->getInventory()->addItem(Item::get(Item::QUARTZ, 0, 1));
						 } elseif($event->getBlock()->getId() === 15) {
                            $event->getPlayer()->getInventory()->addItem(Item::get(Item::IRON_INGOT, 0, 1));
					    } elseif($event->getBlock()->getId() === 14) {	
                            $event->getPlayer()->getInventory()->addItem(Item::get(Item::GOLD_INGOT, 0, 1));
                        } else {
                            $event->getPlayer()->getInventory()->addItem(Item::get($event->getBlock()->getId(), $event->getBlock()->getDamage(), 1));
                        }
                    }
                } else {
                    $event->setDrops(array(Item::get(Item::AIR, 0, 0)));
                    $event->getPlayer()->getInventory()->addItem(Item::get($event->getBlock()->getId(), $event->getBlock()->getDamage(), 1));
				    }    
			#}
				}
			}
   }
	//自動TP回去出生點
	public function onPlayerLogin(PlayerLoginEvent $event){
		$event->getPlayer()->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
	}
			 
	//創造禁止丟物品	 
     public function onSD(PlayerDropItemEvent $e){
     $p=$e->getPlayer();
     if($e->getPlayer()->getGamemode(1)){
     $e->setCancelled(true);
     $p->sendMessage("§a創造模式不可丟物品喔~");
	}
}
 
 	//防爆
		public function onExplosion(ExplosionPrimeEvent $event){
		if($event->getEntity() instanceof PrimedTNT){
			$event->setCancelled(true);
		}
	}
	
		//防熊代碼
	public function onBlockUpdate(BlockUpdateEvent $event){
		$block = $event->getBlock();
		if(($block instanceof Water) OR ($block instanceof Lava)){
			$event->setCancelled(true);
		}
	}
 
     //玩家進服提示
     public function onPlayerJoin(PlayerJoinEvent $event){
    	$player = $event->getPlayer();
        $name = $player->getName();
		
		$purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
		$group = $purePerms->getUserDataMgr()->getData($player)['group'];
        
        $pchat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
        $prefix = $pchat->getPrefix($player);
		
	   $player->getLevel()->addSound(new EndermanTeleportSound($player->getLocation()));
	   $player->sendMessage("§e你可以輸入§a/menu§e查看常用菜單");
	   $event->setJoinMessage("§l§bJoin >>> §a<§6{$prefix}§a>§e[§d{$group}§e]§c{$name} 加入了遊戲");
    }	
	
	//踢出事件
	public function onPlayerKick(PlayerKickEvent $event){
		
		$player = $event->getPlayer();
		$name = $player->getName();
		$this->getServer()->broadcastMessage("§cKick > §b{$name} §c被踢出伺服器囉OuO");		
	}
	
	//玩家離開提示
	    public function onPlayerQuit(PlayerQuitEvent $event){
    	$player = $event->getPlayer();
        $name = $player->getName();
		 
		$purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
		$group = $purePerms->getUserDataMgr()->getData($player)['group'];
		
        $pchat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
        $prefix = $pchat->getPrefix($player);
		
	    $player->getLevel()->addSound(new EndermanTeleportSound($player->getLocation()));		
	    $event->setQuitMessage("§l§bQuit >>> §a<§6{$prefix}§a>§e[§d{$group}§e]§c{$name} 離開了遊戲");
    }	

	public function onDisable() : void{
		$this->getLogger()->info("SEXDCore Shutdown Completed!");
	}
}
