<?php

namespace EggWars;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as C;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

class EggWars extends PluginBase implements Listener {

public $prefix = C::DARK_AQUA . "[EggWars]";

public function onEnable(){
 $this->getServer->getPluginManager->registerEvents($this, $this)
 $this->getLogger->info("EggWars enabled");

public function onDisable(){
 $this->getLogger->info("EggWars disabled");
}

public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
 if(!$sender instanceof Player) {
  return;
 }
 if($strtolower($args[0]))
}