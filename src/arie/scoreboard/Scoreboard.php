<?php
/*
 * Copyright (c) 2022 Arisify
 *
 * This program is freeware, so you are free to redistribute and/or modify
 * it under the conditions of the MIT License.
 *
 *  /\___/\
 *  )     (     @author Arisify
 *  \     /
 *   )   (      @link   https://github.com/Arisify
 *  /     \     @license https://opensource.org/licenses/MIT MIT License
 *  )     (
 * /       \
 * \       /
 *  \__ __/
 *     ))
 *    //
 *   ((
 *    \)
*/
declare(strict_types=1);

namespace arie\scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

class Scoreboard{
	use SingletonTrait;

	private array $scoreboards = [];
	private const MIN_SCORE = 0;
	private const MAX_SCORE = 16;

	public function create(Player $player, string $objectiveName, string $displayName, string $criteriaName = "dummy", int $sortOrder = 0, string $displaySlot = "sidebar") : void{
		if (isset($this->scoreboards[$player->getName()])) {
			$this->remove($player);
		}
		$pk = SetDisplayObjectivePacket::create(
			$displaySlot,
			$objectiveName,
			$displayName,
			$criteriaName,
			$sortOrder
		);
		$player->getNetworkSession()->sendDataPacket($pk);
		$this->scoreboards[$player->getName()] = $objectiveName;
	}

	public function getObjectiveName(Player $player) : ?string{
		return $this->scoreboards[$player->getName()] ?? null;
	}

	public function remove(Player $player) : void{
		$objectiveName = $this->getObjectiveName($player);
		$pk = RemoveObjectivePacket::create($objectiveName);
		$player->getNetworkSession()->sendDataPacket($pk);
		unset($this->scoreboards[$player->getName()]);
	}

	public function setLine(PLayer $player, int $score, string $message, int $type = ScorePacketEntry::TYPE_FAKE_PLAYER) : bool{
		assert($score < self::MAX_SCORE && $score > self::MIN_SCORE, "Line must be greater than " . self::MIN_SCORE . " and smaller than " . self::MAX_SCORE);
		if (isset($this->scoreboards[$player->getName()])) {
			return false;
		}
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $this->getObjectiveName($player);
		$entry->type = $type;
		$entry->customName = $message;
		$entry->score = $score;
		$entry->scoreboardId = $score;

		$pk = SetScorePacket::create(SetScorePacket::TYPE_CHANGE, [$entry]);
		$player->getNetworkSession()->sendDataPacket($pk);
		return true;
	}
}
