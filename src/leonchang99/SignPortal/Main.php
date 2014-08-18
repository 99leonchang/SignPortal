<?php

namespace leonchang99\SignPortal;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
/** Not currently used but may be later used  */
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\tile\Tile;

class Main extends PluginBase implements Listener{
    private $api, $server, $path;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function playerBlockTouch(PlayerInteractEvent $event){
        if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
            if(!($sign instanceof Sign)){
                return;
            }
            $sign = $sign->getText();
            if($sign[0]=='[WORLD]'){
                if(isset($sign[1])){
                    $mapname = $sign[1];
                    if(Server::getInstance()->loadLevel($mapname) != false){
                        $event->getPlayer()->teleport(Server::getInstance()->getLevelByName($mapname)->getSafeSpawn());
                    }
                    //Prevents most crashes
                    $event->getPlayer()->sendMessage("[SignPortal] World '".$mapname."' not found.");
                }
            }
        }
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        //Commands are just for development only, tread carefully...
        switch($command->getName()){
            //Very basic world generation command for world teleportation testing
            case "generate":
                if(isset($args[0])){
                    Server::getInstance()->generateLevel($args[0]);
                    $sender->sendMessage("[SignPortal] World ".$args[0]." is being generated");
                }else{
                    $sender->sendMessage("Usage /generate <worldname>");
                }
                return true;
            default:
                return false;
        }
    }

    /** Stuff for next update once SignChangeEvent is implemented */
    /**
    public function tileupdate(SignChangeEvent $event){
        $sign = $event->getBlock()->getLevel()->getTile(new Vector3($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z))->getText();
        if($sign[0]=='[WORLD]'){
            if($event->getPlayer()->isOp()){
                if(isset($sign[1])){
                    if(Server::getInstance()->loadLevel($sign[1])!==false){
                        $event->getPlayer()->sendMessage("[SignPortal] Portal to world '".$sign[1]."' created");
                        return true;
                    }
                    $event->getPlayer()->sendMessage("[SignPortal] World '".$sign[1]."' does not exist!");
                    return false;
                }
                $event->getPlayer()->sendMessage("[SignPortal] World name not set");
                return false;
            }
        $event->getPlayer()->sendMessage("[SignPortal] You must be an OP to make a portal");
        return false;
        }
    }
    **/
}
