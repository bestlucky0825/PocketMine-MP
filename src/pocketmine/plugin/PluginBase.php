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

namespace pocketmine\plugin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\Config;

abstract class PluginBase implements Plugin{

	/** @var PluginLoader */
	private $loader;

	/** @var Server */
	private $server;

	/** @var bool */
	private $isEnabled = false;

	/** @var PluginDescription */
	private $description;

	/** @var string */
	private $dataFolder;
	/** @var Config|null */
	private $config = null;
	/** @var string */
	private $configFile;
	/** @var string */
	private $file;

	/** @var PluginLogger */
	private $logger;

	/** @var TaskScheduler */
	private $scheduler;

	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file){
		$this->loader = $loader;
		$this->server = $server;
		$this->description = $description;
		$this->dataFolder = rtrim($dataFolder, "\\/") . "/";
		$this->file = rtrim($file, "\\/") . "/";
		$this->configFile = $this->dataFolder . "config.yml";
		$this->logger = new PluginLogger($this);
		$this->scheduler = new TaskScheduler($this->logger, $this->getFullName());

		$this->onLoad();
	}

	/**
	 * Called when the plugin is loaded, before calling onEnable()
	 */
	protected function onLoad()/* : void /* TODO: uncomment this for next major version */{

	}

	/**
	 * Called when the plugin is enabled
	 */
	protected function onEnable()/* : void /* TODO: uncomment this for next major version */{

	}

	/**
	 * Called when the plugin is disabled
	 * Use this to free open things and finish actions
	 */
	protected function onDisable()/* : void /* TODO: uncomment this for next major version */{

	}

	/**
	 * @return bool
	 */
	final public function isEnabled() : bool{
		return $this->isEnabled;
	}

	/**
	 * @param bool $enabled
	 */
	final public function setEnabled(bool $enabled = true) : void{
		if($this->isEnabled !== $enabled){
			$this->isEnabled = $enabled;
			if($this->isEnabled){
				$this->onEnable();
			}else{
				$this->onDisable();
			}
		}
	}

	/**
	 * @return bool
	 */
	final public function isDisabled() : bool{
		return !$this->isEnabled;
	}

	final public function getDataFolder() : string{
		return $this->dataFolder;
	}

	final public function getDescription() : PluginDescription{
		return $this->description;
	}

	/**
	 * @return PluginLogger
	 */
	public function getLogger() : PluginLogger{
		return $this->logger;
	}

	/**
	 * @param string $name
	 *
	 * @return Command|PluginIdentifiableCommand|null
	 */
	public function getCommand(string $name){
		$command = $this->getServer()->getPluginCommand($name);
		if($command === null or $command->getPlugin() !== $this){
			$command = $this->getServer()->getPluginCommand(strtolower($this->description->getName()) . ":" . $name);
		}

		if($command instanceof PluginIdentifiableCommand and $command->getPlugin() === $this){
			return $command;
		}else{
			return null;
		}
	}

	/**
	 * @param CommandSender $sender
	 * @param Command       $command
	 * @param string        $label
	 * @param string[]      $args
	 *
	 * @return bool
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		return false;
	}

	/**
	 * @return bool
	 */
	protected function isPhar() : bool{
		return strpos($this->file, "phar://") === 0;
	}

	/**
	 * Gets an embedded resource on the plugin file.
	 * WARNING: You must close the resource given using fclose()
	 *
	 * @param string $filename
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename){
		$filename = rtrim(str_replace("\\", "/", $filename), "/");
		if(file_exists($this->file . "resources/" . $filename)){
			return fopen($this->file . "resources/" . $filename, "rb");
		}

		return null;
	}

	/**
	 * Saves an embedded resource to its relative location in the data folder
	 *
	 * @param string $filename
	 * @param bool $replace
	 *
	 * @return bool
	 */
	public function saveResource(string $filename, bool $replace = false) : bool{
		if(trim($filename) === ""){
			return false;
		}

		if(($resource = $this->getResource($filename)) === null){
			return false;
		}

		$out = $this->dataFolder . $filename;
		if(!file_exists(dirname($out))){
			mkdir(dirname($out), 0755, true);
		}

		if(file_exists($out) and !$replace){
			return false;
		}

		$ret = stream_copy_to_stream($resource, $fp = fopen($out, "wb")) > 0;
		fclose($fp);
		fclose($resource);
		return $ret;
	}

	/**
	 * Returns all the resources packaged with the plugin in the form ["path/in/resources" => SplFileInfo]
	 *
	 * @return \SplFileInfo[]
	 */
	public function getResources() : array{
		$resources = [];
		if(is_dir($this->file . "resources/")){
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file . "resources/")) as $resource){
				if($resource->isFile()){
					$path = str_replace(DIRECTORY_SEPARATOR, "/", substr((string) $resource, strlen($this->file . "resources/")));
					$resources[$path] = $resource;
				}
			}
		}

		return $resources;
	}

	/**
	 * @return Config
	 */
	public function getConfig() : Config{
		if($this->config === null){
			$this->reloadConfig();
		}

		return $this->config;
	}

	public function saveConfig(){
		if(!$this->getConfig()->save()){
			$this->getLogger()->critical("Could not save config to " . $this->configFile);
		}
	}

	public function saveDefaultConfig() : bool{
		if(!file_exists($this->configFile)){
			return $this->saveResource("config.yml", false);
		}
		return false;
	}

	public function reloadConfig(){
		if(!$this->saveDefaultConfig()){
			@mkdir($this->dataFolder);
		}
		$this->config = new Config($this->configFile);
		if(($configStream = $this->getResource("config.yml")) !== null){
			$this->config->setDefaults(yaml_parse(Config::fixYAMLIndexes(stream_get_contents($configStream))));
			fclose($configStream);
		}
	}

	/**
	 * @return Server
	 */
	final public function getServer() : Server{
		return $this->server;
	}

	/**
	 * @return string
	 */
	final public function getName() : string{
		return $this->description->getName();
	}

	/**
	 * @return string
	 */
	final public function getFullName() : string{
		return $this->description->getFullName();
	}

	/**
	 * @return string
	 */
	protected function getFile() : string{
		return $this->file;
	}

	/**
	 * @return PluginLoader
	 */
	public function getPluginLoader(){
		return $this->loader;
	}

	/**
	 * @return TaskScheduler
	 */
	public function getScheduler() : TaskScheduler{
		return $this->scheduler;
	}
}
