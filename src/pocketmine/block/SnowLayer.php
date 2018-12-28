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

namespace pocketmine\block;

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\TieredTool;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class SnowLayer extends Flowable{

	protected $id = self::SNOW_LAYER;

	/** @var int */
	protected $layers = 1;

	public function __construct(){

	}

	protected function writeStateToMeta() : int{
		return $this->layers - 1;
	}

	public function readStateFromMeta(int $meta) : void{
		$this->layers = BlockDataValidator::readBoundedInt("layers", $meta + 1, 1, 8);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getName() : string{
		return "Snow Layer";
	}

	public function canBeReplaced() : bool{
		return true;
	}

	public function getHardness() : float{
		return 0.1;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHOVEL;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($blockReplace->getSide(Facing::DOWN)->isSolid()){
			//TODO: fix placement
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide(Facing::DOWN)->isSolid()){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), false);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->level->getBlockLightAt($this->x, $this->y, $this->z) >= 12){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), false);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(Item::SNOWBALL) //TODO: check layer count
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
