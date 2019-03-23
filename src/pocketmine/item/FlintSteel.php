<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use function assert;

class FlintSteel extends Tool{
	public function __construct(){
		parent::__construct(self::FLINT_STEEL, 0, "Flint and Steel");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		if($blockReplace->getId() === BlockLegacyIds::AIR){
			$level = $player->getLevel();
			assert($level !== null);
			$level->setBlock($blockReplace, BlockFactory::get(BlockLegacyIds::FIRE));
			$level->broadcastLevelSoundEvent($blockReplace->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_IGNITE);

			$this->applyDamage(1);

			return ItemUseResult::SUCCESS();
		}

		return ItemUseResult::NONE();
	}

	public function getMaxDurability() : int{
		return 65;
	}
}
