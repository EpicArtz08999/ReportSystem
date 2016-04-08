<?php

namespace ReportSystem;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener
{
    public $pref = TextFormat::GRAY . ">> " . TextFormat::RED . "Report" . TextFormat::GRAY . " | ";
    public $headline = TextFormat::GRAY . "[+]- - - - " . TextFormat::RED . "Report" . TextFormat::GRAY . " - - - -[+]";
    public $times = array();

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->reloadConfig();
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        if (empty($this->times[$name])) {
            $this->times[$name] = 0;
        }
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
    {
        switch ($cmd->getName()) {
            case "report":
                $time = time();
                $name = strtolower($sender->getName());
                if (($time - $this->times[$name]) >= $this->getConfig()->get("waiting_time")) {
                    if (!empty($args[0]) and !empty($args[1])) {
                        if (strtolower($args[0]) === strtolower($sender->getName())) {
                            $sender->sendMessage($this->pref . TextFormat::YELLOW . "Du kannst dich nicht selber reporten.");
                            return true;
                        }
                        if ($player = $this->getServer()->getPlayer($args[0])) {
                            $sender->sendMessage($this->pref . TextFormat::YELLOW . "Du hast erfolgreich " . TextFormat::AQUA . $player->getName() . TextFormat::YELLOW . " reportet!");
                            $this->times[$name] = time();
                            array_shift($args);
                            $reason = implode(" ", $args);
                            foreach ($this->getServer()->getOnlinePlayers() as $pl) {
                                if ($pl->hasPermission("ReportSystem.check")) {
                                    $pl->sendMessage($this->headline);
                                    $pl->sendMessage(TextFormat::AQUA . $player->getName() . TextFormat::YELLOW . " wurde von " . TextFormat::AQUA . $sender->getName() . TextFormat::YELLOW . " reportet.");
                                    $pl->sendMessage(TextFormat::YELLOW . "Grund: " . TextFormat::AQUA . $reason);
                                }
                            }
                            return true;
                        } else {
                            $sender->sendMessage($this->pref . TextFormat::YELLOW . "Dieser Spieler($args[0]) ist offline.");
                            return true;
                        }
                    } else {
                        $sender->sendMessage($this->pref . TextFormat::YELLOW . "Benutze: " . $cmd->getUsage());
                        return true;
                    }
                } else {
                    $time_to_wait = $this->getConfig()->get("waiting_time");
                    $time_waited = $time - $this->times[$name];
                    $t = $time_to_wait - $time_waited;
                    $sender->sendMessage($this->pref . TextFormat::YELLOW . "Du musst noch $t Sekunden warten, bis du wieder reporten kannst.");
                    return true;
                }
        }
    }
}