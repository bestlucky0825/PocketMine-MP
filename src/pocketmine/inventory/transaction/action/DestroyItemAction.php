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

namespace pocketmine\inventory\transaction\action;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;

/**
 * This action type shows up when a creative player puts an item into the creative inventory menu to destroy it.
 * The output is the item destroyed. You can think of this action type like setting an item into /dev/null
 */
class DestroyItemAction extends InventoryAction{

	public function __construct(Item $targetItem){
		parent::__construct(ItemFactory::air(), $targetItem);
	}

	public function isValid(Player $source) : bool{
		return !$source->hasFiniteResources();
	}

	public function execute(Player $source) : void{
		//NOOP
	}
}
