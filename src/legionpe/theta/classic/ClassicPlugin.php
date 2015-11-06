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
use legionpe\theta\classic\battle\BattleTask;
use legionpe\theta\classic\battle\ClassicBattle;
use legionpe\theta\classic\battle\queue\QueueManager;
use legionpe\theta\classic\battle\queue\QueueTask;
use legionpe\theta\classic\commands\BattleCommand;
use legionpe\theta\classic\commands\PvpStatsCommand;
use legionpe\theta\classic\commands\PvpTopCommand;
use legionpe\theta\classic\commands\TeleportHereCommand;
use legionpe\theta\classic\commands\TeleportToCommand;
use legionpe\theta\classic\query\ClassicLoginDataQuery;
use legionpe\theta\classic\query\ClassicSaveSinglePlayerQuery;
use legionpe\theta\command\session\friend\FriendlyFireActivationCommand;
use legionpe\theta\queue\Queue;
use pocketmine\Player;

//use legionpe\theta\classic\commands\OneVsOneCommand;

class ClassicPlugin extends BasePlugin{
	/** @var ClassicBattle[] */
	public $battles = [];
	/** @var QueueManager */
	private $queueManager;
	/** @var TeleportManager */
	private $tpMgr;
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
		$this->queueManager = new QueueManager($this);
		$this->tpMgr = new TeleportManager($this);
		$this->getServer()->getCommandMap()->registerAll("c", [
			new TeleportHereCommand($this),
			new TeleportToCommand($this),
			new FriendlyFireActivationCommand($this),
			new PvpStatsCommand($this),
			new PvpTopCommand($this),
			new BattleCommand($this)
//			new OneVsOneCommand($this),
		]);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new BattleTask($this), 20);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new QueueTask($this), 400);
		new FireballTask($this);
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
	/**
	 * @return QueueManager
	 */
	public function getQueueManager(){
		return $this->queueManager;
	}
	/**
	 * @return TeleportManager
	 */
	public function getTeleportManager(){
		return $this->tpMgr;
	}
	/**
	 * @return ClassicBattle[]
	 */
	public function getBattles(){
		return $this->battles;
	}
	/**
	 * @param ClassicBattle $battle
	 */
	public function addBattle(ClassicBattle $battle){
		$this->battles[$battle->getId()] = $battle;
	}
	/**
	 * @param \legionpe\theta\classic\battle\ClassicBattle $battle
	 */
	public function removeBattle(ClassicBattle $battle){
		unset($this->battles[$battle->getId()]);
	}
	/**
	 * @param $id
	 * @return ClassicBattle|null
	 */
	public function getBattleById($id){
		return isset($this->battles[$id]) ? $this->battles[$id] : null;
	}
	protected function createSession(Player $player, array $loginData){
		return new ClassicSession($this, $player, $loginData);
	}
}
