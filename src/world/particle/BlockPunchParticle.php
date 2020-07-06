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

namespace pocketmine\world\particle;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

/**
 * This particle appears when a player is attacking a block face in survival mode attempting to break it.
 */
class BlockPunchParticle implements Particle{

	/** @var Block */
	private $block;
	/** @var int */
	private $face;

	public function __construct(Block $block, int $face){
		$this->block = $block;
		$this->face = $face;
	}

	public function encode(Vector3 $pos){
		return LevelEventPacket::create(LevelEventPacket::EVENT_PARTICLE_PUNCH_BLOCK, RuntimeBlockMapping::getInstance()->toRuntimeId($this->block->getId(), $this->block->getMeta()) | ($this->face << 24), $pos);
	}
}
