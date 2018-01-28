<?php

/*
 *    _____                __        __
 *   | ____|  __ _    __ _ \ \      / /__ _  _ __  ___
 *   |  _|   / _` | / _` |  \ \ /\ / // _` || '__|/ __|
 *   | |___ | (_| || (_| |   \ V  V /| (_| || |   \__ \
 *   |_____| \__, | \__, |    \_/\_/  \__,_||_|   |___/
 *           |___/  |___/
 */

declare(strict_types = 1);

namespace eggwars\arena\scheduler;

use eggwars\arena\Arena;
use eggwars\arena\scheduler\generator\GenChestInventory;
use eggwars\EggWars;
use eggwars\LevelManager;
use eggwars\scheduler\EggWarsTask;
use pocketmine\block\Block;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use function Sodium\library_version_major;

/**
 * Class GeneratorScheduler
 * @package eggwars\arena\scheduler
 */
class GeneratorScheduler extends EggWarsTask implements Listener {

    // line 1
    const LINE_1 = "§0§lGenerator";

    // line 2
    const LINE_2_DIAMOND = "§bDiamond";
    const LINE_2_GOLD = "§6Gold";
    const LINE_2_IRON = "§7Iron";

    // line 3
    const LINE_3 = "§0Level %level";

    // line 4
    const LINE_4 = "§8Right click";

    const IRON = 1;
    const GOLD = 2;
    const DIAMOND = 3;


    /**
     * HOW TO SET? :D
     *
     * 1 => EggWars
     * 2 => Iron | Gold | Diamond
     * 3 => 0 | 1 | 2 | 3 | 4 <-- only at gold and iron
     *
     */

    /** @var  Arena $plugin */
    private $arena;

    /** @var null|\pocketmine\level\Level  */
    private $level;

    /** @var int $tick */
    private $tick;

    /**
     * GeneratorScheduler constructor.
     * @param Arena $arena
     */
    public function __construct(Arena $arena) {
        $this->arena = $arena;
        $this->getPlugin()->getServer()->getPluginManager()->registerEvents($this, $this->getPlugin());
        $this->checkSigns($arena->getLevel());
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        if(!$this->getArena()->getLevel() instanceof Level) return;
        $this->tick++;
        if($this->getArena()->getPhase() == 1) {
            if(!$this->getArena()->isEnabled()) return;
            $this->spawn();
        }
    }

    private function spawn() {
        $this->dropIron();
        $this->dropGold();
        $this->dropDiamond();
    }


    /**
     * @param Level $level
     */
    public function checkSigns(Level $level) {
        foreach ($level->getTiles() as $tile) {
            if($tile instanceof Sign) {
                $this->loadSign($tile);
            }
        }
    }

    /**
     * @param Level $level
     * @param int $material
     * @return Sign[] $signs
     */
    public function getSigns(Level $level, int $material): array {
        /** @var Sign[] $levelSigns */
        $levelSigns = [];
        foreach ($level->getTiles() as $tile) {
            if($tile instanceof Sign && $tile->getText()[0] == self::LINE_1) {
                array_push($levelSigns, $tile);
            }
        }
        /** @var Sign[] $signs */
        $signs = [];
        foreach ($levelSigns as $sign) {
            if(($material == self::IRON) && ($sign->getText()[1] == self::LINE_2_IRON)) {
                array_push($signs, $sign);
            }
            if(($material == self::GOLD) && ($sign->getText()[1] == self::LINE_2_GOLD)) {
                array_push($signs, $sign);
            }
            if(($material == self::DIAMOND) && ($sign->getText()[1] == self::LINE_2_DIAMOND)) {
                array_push($signs, $sign);
            }
        }
        return $signs;
    }

    public function dropIron() {
        $signs = $this->getSigns($this->getArena()->getLevel(), self::IRON);
        foreach ($signs as $sign) {
            $level = intval(str_replace("§0Level ", "", $sign->getText()[2]));
            switch (strval($level)) {
                case "1":
                    // 3 sec
                    if($this->tick%60 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::IRON_INGOT));
                    }
                    break;
                case "2":
                    // 2 sec
                    if($this->tick%40 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::IRON_INGOT));
                    }
                    break;
                case "3":
                    // 1 sec
                    if($this->tick%20 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::IRON_INGOT));
                    }
                    break;
                case "5":
                    // 0.5 sec
                    if($this->tick%10 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::IRON_INGOT));
                    }
                    break;
                default:
                    break;
            }
        }
    }

    public function dropGold() {
        $signs = $this->getSigns($this->getArena()->getLevel(), self::GOLD);
        foreach ($signs as $sign) {
            $level = strval(str_replace("§0Level ", "", $sign->getText()[2]));
            switch (strval($level)) {
                case "1":
                    // 5 sec
                    if($this->tick%100 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::GOLD_INGOT));
                    }
                    break;
                case "2":
                    // 3 sec
                    if($this->tick%60 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::GOLD_INGOT));
                    }
                    break;
                case "3":
                    // 2.5 sec
                    if($this->tick%50 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::GOLD_INGOT));
                    }
                    break;
                case "4":
                    // 2 sec
                    if($this->tick%40 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::GOLD_INGOT));
                    }
                    break;
                default:
                    break;
            }
        }
    }

    public function dropDiamond() {
        $signs = $this->getSigns($this->getArena()->getLevel(), self::DIAMOND);
        foreach ($signs as $sign) {
            $level = intval(str_replace("§0Level ", "", $sign->getText()[2]));
            switch (strval($level)) {
                case "1":
                    // 20 sec
                    if($this->tick%400 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::DIAMOND));
                    }
                    break;
                case "2":
                    // 15 sec
                    if($this->tick%300 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::DIAMOND));
                    }
                    break;
                case "3":
                    // 10 sec
                    if($this->tick%200 == 0) {
                        $this->getArena()->getLevel()->dropItem($sign->asVector3(), Item::get(Item::DIAMOND));
                    }
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param Sign $sign
     */
    private function loadSign(Sign $sign) {
        $text = $sign->getText();
        if($text[0] != "EggWars") return;

        $gen = $text[1];
        $lvl = $text[2];
        if(!in_array($gen, ["Gold", "Iron", "Diamond"])) return;
        if(!in_array(intval($lvl), [0, 1, 2, 3, 4, 5])) return;
        switch ($gen) {
            case "Iron":
                $sign->setText(self::LINE_1, self::LINE_2_IRON, str_replace("%level", $lvl, self::LINE_3), self::LINE_4);
                break;
            case "Gold":
                $sign->setText(self::LINE_1, self::LINE_2_GOLD, str_replace("%level", $lvl, self::LINE_3), self::LINE_4);
                break;
            case "Diamond":
                $sign->setText(self::LINE_1, self::LINE_2_DIAMOND, str_replace("%level", $lvl, self::LINE_3), self::LINE_4);
                break;
        }
    }

    private function debug($msg) {
        $this->getPlugin()->getLogger()->critical("DBG & {$msg}");
}

    public function onTouch(PlayerInteractEvent $event) {

        $player = $event->getPlayer();

        if(!$this->getArena()->inGame($player)) {
            return;
        }

        $tile = $event->getBlock()->getLevel()->getTile($event->getBlock()->asVector3());

        if(!$tile instanceof Sign) {
            return;
        }
        if($tile->getText()[0] !== self::LINE_1) {
            $this->debug("1");
            return;
        }

        $text = $tile->getText();
        $type = null;

        switch ($text[1]) {
            case self::LINE_2_IRON:
                $type = self::IRON;
                break;
            case self::LINE_2_GOLD:
                $type = self::GOLD;
                break;
            case self::LINE_2_DIAMOND:
                $type = self::DIAMOND;
                break;
        }

        if($type === null) {
            $this->debug("2");
            return;

        }

        $level = 0;
        level:
        if($level >= 4) {
            $this->debug("3");
            return;

        }
        if($tile->getText()[2] != str_replace("%level", $level, self::LINE_3)) {
            $level++;
            goto level;
        }

        $this->createUpdateWindow($player, $type, $level);
    }

    public function onTransaction(InventoryTransactionEvent $event) {

        /** @var GenChestInventory $inv */
        $inv = null;

        foreach ($event->getTransaction()->getInventories() as $inventory) {
            if($inventory instanceof GenChestInventory) {
                $inv = $inventory;
            }
        }

        if($inv === null) {
            return;
        }

        /** @var Player $player */
        $player = null;

        foreach ($inv->getViewers() as $viewer) {
            $player = $viewer;
        }

        if($player === null) {
            return;
        }

        /** @var Item $targetItem */
        $targetItem = null;

        foreach ($event->getTransaction()->getActions() as $action) {
            $targetItem = $action->getTargetItem();
        }

        if($targetItem === null) {
            return;
        }

        if($targetItem->getId() == 0) {
            $event->setCancelled(true);
            return;
        }

        if($inv->genType === null || $inv->genLevel === null || $inv->gensigntile === null) {
            $event->setCancelled(true);
            return;
        }

        $price = $this->getPriceItem($inv->genType, $inv->genLevel);

        if($targetItem->getId()==Item::EXPERIENCE_BOTTLE) {
            if($player->getInventory()->contains($price)) {
                /** @var Sign $tile */
                $tile = $inv->gensigntile;
                $tile->setText(self::LINE_1, str_replace(strval($inv->genLevel), strval($inv->genLevel+1), $tile->getText()[1]), $tile->getText()[2], $tile->getText()[3]);
                $player->sendMessage(EggWars::getPrefix()."§aGenerator updated!");
            }
            else {
                $player->sendMessage(EggWars::getPrefix(). "§cYou does not have too enough materials!");
            }
        }
        $event->setCancelled(true);

    }

    public function createUpdateWindow(Player $player, int $ingot, $genLevel) {
        $this->debug("4");
        $nbt = new CompoundTag('', [
            new StringTag('id', Tile::CHEST),
            new StringTag('CustomName', "§3§lEggWars §7>>> §6Generator"),
            new IntTag('x', $x = intval($player->getX())),
            new IntTag('y', $y = intval($player->getY()) + 4),
            new IntTag('z', $z = intval($player->getZ()))
        ]);
        $inv = new GenChestInventory(new Chest($player->getLevel(), $nbt));
        $block = Block::get(Block::CHEST);
        $block->setComponents($x, $y, $z);
        $player->getLevel()->sendBlocks([$player], [$block]);

        $time = strval($this->ticks[$ingot][$genLevel]);
        $player->addWindow($inv);
        switch ($ingot) {
            case self::IRON:
                $inv->setItem(11, Item::get(Item::IRON_INGOT)->setCustomName("§7Iron generator\n§blevel: {$genLevel}\n§btime: {$time} sec."));
                break;
            case self::GOLD:
                $inv->setItem(11, Item::get(Item::GOLD_INGOT)->setCustomName("§6Gold generator\n§blevel: {$genLevel}\n§btime: {$time} sec."));
                break;
            case self::DIAMOND:
                $inv->setItem(11, Item::get(Item::DIAMOND)->setCustomName("§bDiamond generator\n§blevel: {$genLevel}\n§btime: {$time} sec."));
        }
        $inv->setItem(14, Item::get(Item::EXPERIENCE_BOTTLE)->setCustomName($this->getUpdatedDescription($ingot, $genLevel)));
    }

    public function getUpdatedDescription($ingot, $genLevel): string {
        $text = "";
        $level = $genLevel++;
        $time = 0;
        if(empty($this->ticks[$ingot][$level])) {
            $level = "max";
        }
        else {
            $time = $this->ticks[$ingot][$level];
        }
        $pItem = $this->getPriceItem($ingot, $genLevel);
        $update = $pItem->getName()." ".$pItem->getCount()."x";
        switch ($ingot) {
            case self::IRON:
                $text = "§7Iron Generator\n§blevel {$level}\ntime: {$time}\n§bupdate: {$update}";
                break;
            case self::GOLD:
                $text = "§6Gold Generator\n§blevel {$level}\ntime: {$time}\n§bupdate: {$update}";
                break;
            case self::DIAMOND:
                $text = "§6Gold Generator\n§blevel {$level}\ntime: {$time}\n§bupdate: {$update}";
                break;
        }
        return $text;
    }

    public function getPriceItem($ingot, $genLevel): Item {
        switch ($ingot) {
            case self::IRON:
                switch ($genLevel) {
                    case 1:
                        return Item::get(Item::IRON_INGOT, 0, 20);
                    case 2:
                        return Item::get(Item::IRON_INGOT, 0, 40);
                    case 3:
                        return Item::get(Item::GOLD_INGOT, 0, 30);
                }
                break;
            case self::GOLD:
                switch ($genLevel) {
                    case 1:
                        return Item::get(Item::GOLD_INGOT, 0, 25);
                    case 2:
                        return Item::get(Item::GOLD_INGOT, 0, 40);
                    case 3:
                        return Item::get(Item::DIAMOND, 0, 20);
                }
                break;
            case self::DIAMOND:
                switch ($genLevel) {
                    case 0:
                        return Item::get(Item::DIAMOND, 0, 20);
                    case 1:
                        return Item::get(Item::DIAMOND, 0, 25);
                    case 2:
                        return Item::get(Item::DIAMOND, 0, 30);
                }
                break;

        }
    }


    private $ticks = [
        self::IRON => [
            1 => 3.0,
            2 => 2.0,
            3 => 1.0,
            4 => 0.5
        ],
        self::GOLD => [
            1 => 5.0,
            2 => 3.0,
            3 => 2.5,
            4 => 2.0
        ],
        self::DIAMOND => [
            1 => 20,
            2 => 15,
            3 => 10
        ]
    ];

    /**
     * @return Arena $arena
     */
    public function getArena(): Arena {
        return $this->arena;
    }

    public function getPlugin(): EggWars {
        return EggWars::getInstance();
    }
}