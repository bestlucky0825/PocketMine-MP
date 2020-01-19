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

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class Pond extends Populator{
	/** @var int */
	private $waterOdd = 4;
	/** @var int */
	private $lavaOdd = 4;
	/** @var int */
	private $lavaSurfaceOdd = 4;

	public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random){
		if($random->nextRange(0, $this->waterOdd) === 0){
			$x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 16);
			$y = $random->nextBoundedInt(128);
			$z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 16);
			$pond = new \pocketmine\level\generator\object\Pond($random, BlockFactory::get(Block::WATER));
			if($pond->canPlaceObject($level, $v = new Vector3($x, $y, $z))){
				$pond->placeObject($level, $v);
			}
		}
	}

	/**
	 * @param int $waterOdd
	 *
	 * @return void
	 */
	public function setWaterOdd(int $waterOdd){
		$this->waterOdd = $waterOdd;
	}

	/**
	 * @param int $lavaOdd
	 *
	 * @return void
	 */
	public function setLavaOdd(int $lavaOdd){
		$this->lavaOdd = $lavaOdd;
	}

	/**
	 * @param int $lavaSurfaceOdd
	 *
	 * @return void
	 */
	public function setLavaSurfaceOdd(int $lavaSurfaceOdd){
		$this->lavaSurfaceOdd = $lavaSurfaceOdd;
	}
}
