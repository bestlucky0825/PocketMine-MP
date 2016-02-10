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

namespace pocketmine\entity;

use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\RemovePlayerPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

class Human extends Creature implements ProjectileSource, InventoryHolder{

	const DATA_PLAYER_FLAG_SLEEP = 1;
	const DATA_PLAYER_FLAG_DEAD = 2;

	const DATA_PLAYER_FLAGS = 16;
	const DATA_PLAYER_BED_POSITION = 17;

	/** @var PlayerInventory */
	protected $inventory;

	/** @var UUID */
	protected $uuid;
	protected $rawUUID;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;
	public $eyeHeight = 1.62;

	protected $skinName;
	protected $skin;

	public function getSkinData(){
		return $this->skin;
	}

	public function getSkinName(){
		return $this->skinName;
	}

	/**
	 * @return UUID|null
	 */
	public function getUniqueId(){
		return $this->uuid;
	}

	/**
	 * @return string
	 */
	public function getRawUniqueId(){
		return $this->rawUUID;
	}

	/**
	 * @param string $str
	 * @param string $skinName
	 */
	public function setSkin($str, $skinName){
		$this->skin = $str;
		$this->skinName = $skinName;
	}

	public function getFood() : float{
		return (float) $this->attributeMap->getAttribute(Attribute::HUNGER)->getValue();
	}

	public function setFood(float $food){
		$this->attributeMap->getAttribute(Attribute::HUNGER)->setValue($food);
	}

	public function addFood(float $amount){
		$this->attributeMap->getAttribute(Attribute::HUNGER)->setValue($amount, true);
	}

	public function getSaturation() : float{
		return $this->attributeMap->getAttribute(Attribute::HUNGER)->getValue();
	}

	public function setSaturation(float $saturation){
		$this->attributeMap->getAttribute(Attribute::HUNGER)->setValue($saturation);
	}

	public function addSaturation(float $amount){
		$this->attributeMap->getAttribute(Attribute::SATURATION)->setValue($amount, true);
	}

	public function getExhaustion() : float{
		return $this->attributeMap->getAttribute(Attribute::EXHAUSTION)->getValue();
	}

	public function setExhaustion(float $exhaustion){
		$this->attributeMap->getAttribute(Attribute::EXHAUSTION)->setValue($exhaustion);
	}

	public function exhaust(float $amount){
		$exhaustion = $this->getExhaustion();
		$exhaustion += $amount;

		if($exhaustion >= 4.0){
			$exhaustion -= 4.0;
			$this->setExhaustion($exhaustion);

			$saturation = $this->getSaturation();
			if($saturation > 0){
				$saturation = max(0, $saturation - 1.0);
				$this->setSaturation($saturation);
			}else{
				$food = $this->getFood();
				if($food > 0){
					$food--;
					$this->setFood($food);
				}
			}
		}
	}

	public function getInventory(){
		return $this->inventory;
	}

	protected function initEntity(){

		$this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, false);
		$this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [0, 0, 0]);

		$this->inventory = new PlayerInventory($this);
		if($this instanceof Player){
			$this->addWindow($this->inventory, 0);
		}

		if(!($this instanceof Player)){
			if(isset($this->namedtag->NameTag)){
				$this->setNameTag($this->namedtag["NameTag"]);
			}

			if(isset($this->namedtag->Skin) and $this->namedtag->Skin instanceof CompoundTag){
				$this->setSkin($this->namedtag->Skin["Data"], $this->namedtag->Skin["Name"]);
			}

			$this->uuid = UUID::fromData($this->getId(), $this->getSkinData(), $this->getNameTag());
		}

		if(isset($this->namedtag->Inventory) and $this->namedtag->Inventory instanceof ListTag){
			foreach($this->namedtag->Inventory as $item){
				if($item["Slot"] >= 0 and $item["Slot"] < 9){ //Hotbar
					$this->inventory->setHotbarSlotIndex($item["Slot"], isset($item["TrueSlot"]) ? $item["TrueSlot"] : -1);
				}elseif($item["Slot"] >= 100 and $item["Slot"] < 104){ //Armor
					$this->inventory->setItem($this->inventory->getSize() + $item["Slot"] - 100, NBT::getItemHelper($item));
				}else{
					$this->inventory->setItem($item["Slot"] - 9, NBT::getItemHelper($item));
				}
			}
		}

		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::SATURATION));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXHAUSTION));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HUNGER));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::ATTACK_DAMAGE));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXPERIENCE_LEVEL));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXPERIENCE));

		parent::initEntity();
	}

	public function getName(){
		return $this->getNameTag();
	}

	public function getDrops(){
		$drops = [];
		if($this->inventory !== null){
			foreach($this->inventory->getContents() as $item){
				$drops[] = $item;
			}
		}

		return $drops;
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->Inventory = new ListTag("Inventory", []);
		$this->namedtag->Inventory->setTagType(NBT::TAG_Compound);
		if($this->inventory !== null){
			for($slot = 0; $slot < 9; ++$slot){
				$hotbarSlot = $this->inventory->getHotbarSlotIndex($slot);
				if($hotbarSlot !== -1){
					$item = $this->inventory->getItem($hotbarSlot);
					if($item->getId() !== 0 and $item->getCount() > 0){
						$tag = NBT::putItemHelper($item, $slot);
						$tag->TrueSlot = new ByteTag("TrueSlot", $hotbarSlot);
						$this->namedtag->Inventory[$slot] = $tag;

						continue;
					}
				}

				$this->namedtag->Inventory[$slot] = new CompoundTag("", [
					new ByteTag("Count", 0),
					new ShortTag("Damage", 0),
					new ByteTag("Slot", $slot),
					new ByteTag("TrueSlot", -1),
					new ShortTag("id", 0),
				]);
			}

			//Normal inventory
			$slotCount = Player::SURVIVAL_SLOTS + 9;
			//$slotCount = (($this instanceof Player and ($this->gamemode & 0x01) === 1) ? Player::CREATIVE_SLOTS : Player::SURVIVAL_SLOTS) + 9;
			for($slot = 9; $slot < $slotCount; ++$slot){
				$item = $this->inventory->getItem($slot - 9);
				$this->namedtag->Inventory[$slot] = NBT::putItemHelper($item, $slot);
			}

			//Armor
			for($slot = 100; $slot < 104; ++$slot){
				$item = $this->inventory->getItem($this->inventory->getSize() + $slot - 100);
				if($item instanceof ItemItem and $item->getId() !== ItemItem::AIR){
					$this->namedtag->Inventory[$slot] = NBT::putItemHelper($item, $slot);
				}
			}
		}

		if(strlen($this->getSkinData()) > 0){
			$this->namedtag->Skin = new CompoundTag("Skin", [
				"Data" => new StringTag("Data", $this->getSkinData()),
				"Name" => new StringTag("Name", $this->getSkinName())
			]);
		}
	}

	public function spawnTo(Player $player){
		if($player !== $this and !isset($this->hasSpawned[$player->getLoaderId()])){
			$this->hasSpawned[$player->getLoaderId()] = $player;

			if(strlen($this->skin) < 64 * 32 * 4){
				throw new \InvalidStateException((new \ReflectionClass($this))->getShortName() . " must have a valid skin set");
			}

			if(!($this instanceof Player)){
				$this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getName(), $this->skinName, $this->skin, [$player]);
			}

			$pk = new AddPlayerPacket();
			$pk->uuid = $this->getUniqueId();
			$pk->username = $this->getName();
			$pk->eid = $this->getId();
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->speedX = $this->motionX;
			$pk->speedY = $this->motionY;
			$pk->speedZ = $this->motionZ;
			$pk->yaw = $this->yaw;
			$pk->pitch = $this->pitch;
			$pk->item = $this->getInventory()->getItemInHand();
			$pk->metadata = $this->dataProperties;
			$player->dataPacket($pk);

			$this->inventory->sendArmorContents($player);

			if(!($this instanceof Player)){
				$this->server->removePlayerListData($this->getUniqueId(), [$player]);
			}
		}
	}

	public function despawnFrom(Player $player){
		if(isset($this->hasSpawned[$player->getLoaderId()])){

			$pk = new RemovePlayerPacket();
			$pk->eid = $this->getId();
			$pk->clientId = $this->getUniqueId();
			$player->dataPacket($pk);
			unset($this->hasSpawned[$player->getLoaderId()]);
		}
	}

	public function close(){
		if(!$this->closed){
			if(!($this instanceof Player) or $this->loggedIn){
				foreach($this->inventory->getViewers() as $viewer){
					$viewer->removeWindow($this->inventory);
				}
			}
			parent::close();
		}
	}
}
