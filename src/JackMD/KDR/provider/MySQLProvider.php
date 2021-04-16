<?php
declare(strict_types = 1);

/**
 *   _   _____________
 *  | | / /  _  \ ___ \
 *  | |/ /| | | | |_/ /
 *  |    \| | | |    /
 *  | |\  \ |/ /| |\ \
 *  \_| \_/___/ \_| \_|
 *
 * KDR, a Kill Death Ratio plugin for PocketMine-MP
 * Copyright (c) 2018 JackMD  < https://github.com/JackMD >
 *
 * Discord: JackMD#3717
 * Twitter: JackMTaylor_
 *
 * This software is distributed under "GNU General Public License v3.0".
 * This license allows you to use it and/or modify it but you are not at
 * all allowed to sell this plugin at any cost. If found doing so the
 * necessary action required would be taken.
 *
 * KDR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License v3.0 for more details.
 *
 * You should have received a copy of the GNU General Public License v3.0
 * along with this program. If not, see
 * <https://opensource.org/licenses/GPL-3.0>.
 * ------------------------------------------------------------------------
 */

namespace JackMD\KDR\provider;

use JackMD\KDR\KDR;
use pocketmine\Player;

class MySQLProvider implements ProviderInterface{
	
	/** @var \SQLite3 */
	public $killCounterDB;
	
	public function prepare(): void{
		$this->killCounterDB = new \mysqli("[host]","[user]","[password]","[db]");
    		if($this->killCounterDB->connect_error !== ''){
      		$this->getServer->critical("Cant Conncet to DB! contact pines! and forward this error : " . this->db_error");
		}
		$this->killCounterDB->query("CREATE TABLE IF NOT EXISTS master (player VARCHAR(50) PRIMARY KEY COLLATE NOCASE, kills INT, deaths INT)");
	}
	
	/**
	 * @param Player $player
	 */
	public function registerPlayer(Player $player): void{
		$stmt = $this->killCounterDB->query("INSERT OR REPLACE INTO master (player, kills, deaths) VALUES (".$player->getLowerCaseName().", " 0, 0")");
	}
	
	/**
	 * @param Player $player
	 * @param int    $points
	 */
	public function addDeathPoints(Player $player, int $points = 1): void{
		$stmt = $this->killCounterDB->query("INSERT OR REPLACE INTO master (player, kills, deaths) VALUES (".$player->getLowerCaseName().", ".$this->getPlayerKillPoints($player).", ".$this->getPlayerDeathPoints($player) + $points.")");
	}
	
	/**
	 * @param Player $player
	 * @param int    $points
	 */
	public function addKillPoints(Player $player, int $points = 1): void{
		$stmt = $this->killCounterDB->query("INSERT OR REPLACE INTO master (player, kills, deaths) VALUES (".$player->getLowerCaseName().", ".$this->getPlayerKillPoints($player) + $points.", ".$this->getPlayerDeathPoints($player).")");
	}
	
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function playerExists(Player $player): bool{
		$playerName = $player->getLowerCaseName();
		$result = $this->killCounterDB->query("SELECT player FROM master WHERE player='$playerName';");
		$array = $result->fetchArray(MYSQLI_ASSOC);
		return empty($array) == false;
	}
	
	/**
	 * @param Player $player
	 * @return string
	 */
	public function getKillToDeathRatio(Player $player): string{
		$kills = $this->getPlayerKillPoints($player);
		$deaths = $this->getPlayerDeathPoints($player);
		if($deaths !== 0){
			$ratio = $kills / $deaths;
			if($ratio !== 0){
				return number_format($ratio, 1);
			}
		}
		return "0.0";
	}
	
	/**
	 * @param Player $player
	 * @return int
	 */
	public function getPlayerKillPoints(Player $player): int{
		$playerName = $player->getLowerCaseName();
		$result = $this->killCounterDB->query("SELECT kills FROM master WHERE player = '$playerName'");
		$resultArray = $result->fetchArray(MYSQL_ASSOC);
		return (int) $resultArray["kills"];
	}
	
	/**
	 * @param Player $player
	 * @return int
	 */
	public function getPlayerDeathPoints(Player $player): int{
		$playerName = $player->getLowerCaseName();
		$result = $this->killCounterDB->query("SELECT deaths FROM master WHERE player = '$playerName'");
		$resultArray = $result->fetchArray(MYSQL_ASSOC);
		return (int) $resultArray["deaths"];
	}
	
	public function close(): void{
		$this->killCounterDB->close();
	}
}

