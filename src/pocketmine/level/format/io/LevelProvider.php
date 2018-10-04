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

namespace pocketmine\level\format\io;

use pocketmine\level\format\Chunk;
use pocketmine\math\Vector3;

interface LevelProvider{

	/**
	 * @param string $path
	 */
	public function __construct(string $path);

	/**
	 * Gets the build height limit of this world
	 *
	 * @return int
	 */
	public function getWorldHeight() : int;

	/**
	 * @return string
	 */
	public function getPath() : string;

	/**
	 * Tells if the path is a valid level.
	 * This must tell if the current format supports opening the files in the directory
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function isValid(string $path) : bool;

	/**
	 * Generate the needed files in the path given
	 *
	 * @param string  $path
	 * @param string  $name
	 * @param int     $seed
	 * @param string  $generator
	 * @param array[] $options
	 */
	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []);

	/**
	 * Returns the generator name
	 *
	 * @return string
	 */
	public function getGenerator() : string;

	/**
	 * @return array
	 */
	public function getGeneratorOptions() : array;

	/**
	 * Saves a chunk (usually to disk).
	 *
	 * @param Chunk $chunk
	 */
	public function saveChunk(Chunk $chunk) : void;

	/**
	 * Loads a chunk (usually from disk storage) and returns it. If the chunk does not exist, null is returned.
	 *
	 * @param int $chunkX
	 * @param int $chunkZ
	 *
	 * @return null|Chunk
	 *
	 * @throws \Exception any of a range of exceptions that could be thrown while reading chunks. See individual
	 * implementations for details.
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?Chunk;

	/**
	 * @return string
	 */
	public function getName() : string;

	/**
	 * @return int
	 */
	public function getTime() : int;

	/**
	 * @param int
	 */
	public function setTime(int $value);

	/**
	 * @return int
	 */
	public function getSeed() : int;

	/**
	 * @param int
	 */
	public function setSeed(int $value);

	/**
	 * @return Vector3
	 */
	public function getSpawn() : Vector3;

	/**
	 * @param Vector3 $pos
	 */
	public function setSpawn(Vector3 $pos);

	/**
	 * Returns the world difficulty. This will be one of the Level constants.
	 * @return int
	 */
	public function getDifficulty() : int;

	/**
	 * Sets the world difficulty.
	 * @param int $difficulty
	 */
	public function setDifficulty(int $difficulty);

	/**
	 * Returns the time in ticks to the next rain level change.
	 * @return int
	 */
	public function getRainTime() : int;

	/**
	 * Sets the time in ticks to the next rain level change.
	 * @param int $ticks
	 */
	public function setRainTime(int $ticks) : void;

	/**
	 * @return float 0.0 - 1.0
	 */
	public function getRainLevel() : float;

	/**
	 * @param float $level 0.0 - 1.0
	 */
	public function setRainLevel(float $level) : void;

	/**
	 * Returns the time in ticks to the next lightning level change.
	 * @return int
	 */
	public function getLightningTime() : int;

	/**
	 * Sets the time in ticks to the next lightning level change.
	 * @param int $ticks
	 */
	public function setLightningTime(int $ticks) : void;

	/**
	 * @return float 0.0 - 1.0
	 */
	public function getLightningLevel() : float;

	/**
	 * @param float $level 0.0 - 1.0
	 */
	public function setLightningLevel(float $level) : void;

	/**
	 * Performs garbage collection in the level provider, such as cleaning up regions in Region-based worlds.
	 */
	public function doGarbageCollection();

	/**
	 * Saves information about the level state, such as weather, time, etc.
	 */
	public function saveLevelData();

	/**
	 * Performs cleanups necessary when the level provider is closed and no longer needed.
	 */
	public function close();

	/**
	 * Returns a generator which yields all the chunks in this level.
	 *
	 * @return \Generator|Chunk[]
	 */
	public function getAllChunks() : \Generator;

}
