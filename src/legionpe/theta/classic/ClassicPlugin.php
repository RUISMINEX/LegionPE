<?php

/*
 * LegionPE Theta
 *
 * Copyright (C) 2015 PEMapModder and contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder
 */

namespace legionpe\theta\classic;

use legionpe\theta\BasePlugin;
use legionpe\theta\classic\query\ClassicLoginDataQuery;
use legionpe\theta\classic\query\ClassicSaveSinglePlayerQuery;
use pocketmine\Player;

//use pocketmine\level\Level;
//use pocketmine\utils\Cache;

class ClassicPlugin extends BasePlugin{
	const COINS_ON_KILL = 10;
	protected static function defaultLoginData($uid, Player $player){
		$data = parent::defaultLoginData($uid, $player);
		$data["pvp_init"] = time();
		$data["pvp_kills"] = 0;
		$data["pvp_deaths"] = 0;
		$data["pvp_maxstreak"] = 0;
		$data["pvp_curstreak"] = 0;
		$data["pvp_kit"] = 0;
		return $data;
	}
	public function onEnable(){
		parent::onEnable();
//		echo "fixing grass...\r";
//		if(!$this->getServer()->isLevelLoaded("world_pvp")){
//			$this->getServer()->loadLevel("world_pvp");
//		}
//		foreach($this->getServer()->getLevels() as $level){
//			foreach($level->getChunks() as $chunk){
//				for($x = 0; $x < 16; $x++){
//					for($z = 0; $z < 16; $z++){
//						$chunk->setBiomeColor($x, $z, 0x64, 0xFF, 0x00);
//					}
//				}
//			}
//		}
	}
	public function getLoginQueryImpl(){
		return ClassicLoginDataQuery::class;
	}
	public function getSaveSingleQueryImpl(){
		return ClassicSaveSinglePlayerQuery::class;
	}
	public function sendFirstJoinMessages(Player $player){
		// TODO: Implement sendFirstJoinMessages() method.
	}
	public function query_world(){
		return "pvp-1";
	}
	protected function createSession(Player $player, array $loginData){
		return new ClassicSession($this, $player, $loginData);
	}
}
