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

namespace pocketmine\crafting;

use pocketmine\item\Item;
use function array_map;
use function file_get_contents;
use function json_decode;

final class CraftingManagerFromDataHelper{

	public static function make(string $filePath) : CraftingManager{
		$recipes = json_decode(file_get_contents($filePath), true);
		$result = new CraftingManager();

		$itemDeserializerFunc = \Closure::fromCallable([Item::class, 'jsonDeserialize']);
		foreach($recipes as $recipe){
			switch($recipe["type"]){
				case "shapeless":
					if($recipe["block"] !== "crafting_table"){ //TODO: filter others out for now to avoid breaking economics
						break;
					}
					$result->registerShapelessRecipe(new ShapelessRecipe(
						array_map($itemDeserializerFunc, $recipe["input"]),
						array_map($itemDeserializerFunc, $recipe["output"])
					));
					break;
				case "shaped":
					if($recipe["block"] !== "crafting_table"){ //TODO: filter others out for now to avoid breaking economics
						break;
					}
					$result->registerShapedRecipe(new ShapedRecipe(
						$recipe["shape"],
						array_map($itemDeserializerFunc, $recipe["input"]),
						array_map($itemDeserializerFunc, $recipe["output"])
					));
					break;
				case "smelting":
					if($recipe["block"] !== "furnace"){ //TODO: filter others out for now to avoid breaking economics
						break;
					}
					$result->getFurnaceRecipeManager()->register(new FurnaceRecipe(
						Item::jsonDeserialize($recipe["output"]),
						Item::jsonDeserialize($recipe["input"]))
					);
					break;
				default:
					break;
			}
		}

		return $result;
	}
}