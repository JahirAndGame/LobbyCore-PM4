<?php

declare(strict_types=1);

namespace Lobby\session;

use Lobby\item\ServerSelectorItem;
use Lobby\item\EnderPearlBuffItem;
use Lobby\Main;
use Lobby\utils\Utils;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Session {

    private int $current_ping;
    
    /**
     * Session construct.
     * @param Player $player
     * @param SessionScoreboard $scoreboard
     */
    public function __construct(
        private Player $player,
        private SessionScoreboard $scoreboard
    ) {
        $this->current_ping = $this->getPing();
    }

    private function getPing(): int {
        return $this->player->getNetworkSession()->getPing() ?? 0;
    }

    public function sendWelcomeMessages(): void {
        $config = Main::getInstance()->getConfig();
        $join_message = [
            "§r§f-----------------------------",
            "           " . $config->get("server-name"),
            "§l§dSTORE:§r§f " . $config->get("server-storelink"),
            "§l§bTWITTER:§r§f " . $config->get("server-twitterlink"),
            "§9§lDISCORD:§r§f " . $config->get("server-discordlink"),
            "§4§lYOUTUBE:§r§f " . $config->get("server-youtubelink"),
            "§r§f-----------------------------"
        ];
        $this->player->sendMessage(join("\n", $join_message));
        $this->player->sendTitle($config->get("server-name"), "§6Welcome " . $name = $this->player->getName());
    }

    public function setup(): void {
        $hunger_manager = $this->player->getHungerManager();
        $hunger_manager->setFood($hunger_manager->getMaxFood());
        $this->player->setGamemode(GameMode::ADVENTURE());
        $this->player->setHealth($this->player->getMaxHealth());
        $this->player->getInventory()->setItem(4, new ServerSelectorItem());
        $this->player->getInventory()->setItem(0, new EnderPearlBuffItem());
    }

    public function teleportToLobbyWorld(): void {
        $this->player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
    }

    public function checkPing(): bool {
        $ping = $this->getPing();
        if($this->current_ping !== $ping) {
            $this->current_ping = $ping;
            return true;
        }
        return false;
    }

    public function initScoreboard(): void {
        $this->scoreboard->init();
    }
    
    public function update(): void {
        $this->scoreboard->clear();

        $config = Main::getInstance()->getConfig();
        foreach($config->get('scoreboard.lines') as $content) {
            $content = str_replace(['{players_count}', '{player_ping}', '{player_nick}'], [Utils::getNetworkPlayers(), $this->player->getNetworkSession()->getPing(), $this->player->getName()], $content);
            $this->scoreboard->addLine(TextFormat::colorize($content));
        }
    }

}
