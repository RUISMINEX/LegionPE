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

namespace legionpe\theta\classic\battle;

use legionpe\theta\classic\ClassicPlugin;
use legionpe\theta\classic\ClassicSession;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;

class ClassicBattle{
	CONST STATUS_STARTING = 0, STATUS_RUNNING = 1, STATUS_ENDING = 2;
	private static $nextId = 0;
	/** @var ClassicPlugin */
	private $plugin;
	/** @var int */
	private $id;
	/** @var ClassicSession[][] */
	private $teams = [];
	/** @var int */
	private $currentRound = 0;
	/** @var int */
	private $maxRounds = 3;
	/** @var ClassicSession[] */
	private $roundWinners = [];
	/** @var int[] */
	private $kills = [];
	/** @var int */
	private $roundDuration = 90;
	/** @var int */
	private $time = 0;
	/** @var ClassicBattleKit */
	private $kit;
	/** @var int */
	private $status;
	/** @var \legionpe\theta\classic\battle\ClassicBattleOld[] */
	private $old = [];
	/** @var bool */
	private $canHit = false;

	/**
	 * @param ClassicPlugin $plugin
	 * @param ClassicSession[][] $teams
	 * @param int $rounds
	 * @param int $duration
	 * @param ClassicBattleKit $kit
	 */
	public function __construct(ClassicPlugin $plugin, $teams, $rounds, $duration, ClassicBattleKit $kit){
		$this->plugin = $plugin;
		$this->id = self::$nextId++;
		$this->teams = $teams;
		foreach($teams as $team => $sessions){
			foreach($sessions as $session){
				foreach($plugin->getBattles() as $battle){
					foreach($battle->getSessions() as $hideSession){
						$session->getPlayer()->hidePlayer($hideSession->getPlayer());
						$hideSession->getPlayer()->showPlayer($session->getPlayer());
					}
				}
				$session->setBattle($this);
				$this->kills[$session->getPlayer()->getName()] = 0;
				$this->old[$session->getPlayer()->getName()] = new ClassicBattleOld($session);
			}
		}
		//$plugin->addBattle($this);
		$plugin->battles[$this->id] = $this;
		$this->maxRounds = $rounds;
		$this->roundDuration = $duration;
		$this->kit = $kit;
		$this->setStatus(self::STATUS_STARTING, "Battle starting..");

		// spawn 1: 212 16 22 yaw 150
		// spawn 2: 200 16 3 yaw -30
	}
	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * @return \legionpe\theta\classic\ClassicSession[][]
	 */
	public function getTeam(){
		return $this->teams;
	}
	/**
	 * @return int
	 */
	public function getRound(){
		return $this->currentRound;
	}
	/**
	 * @param $round
	 */
	public function setRound($round){
		$this->currentRound = $round;
	}
	/**
	 * @return int
	 */
	public function getMaxRounds(){
		return $this->maxRounds;
	}
	/**
	 * @param ClassicSession $session
	 */
	public function addRoundWinner(ClassicSession $session){
		$this->roundWinners[] = $session;
	}
	/**
	 * @return string
	 */
	public function getOverallWinner(){
		if(count($this->roundWinners) === 0){
			return "no one";
		}
		$temp = [];
		foreach($this->roundWinners as $roundWinner){
			if(isset($temp[$roundWinner->getPlayer()->getName()])){
				++$temp[$roundWinner->getPlayer()->getName()];
			}else{
				$temp[$roundWinner->getPlayer()->getName()] = 1;
			}
		}
		arsort($temp);
		return key($temp);
	}
	/**
	 * int
	 */
	public function getTeamWinner(){
		if(count($this->roundWinners) === 0){
			return 2;
		}
		$temp = [];
		foreach($this->roundWinners as $roundWinner){
			if(isset($temp[$this->getSessionTeam($roundWinner)])){
				++$temp[$this->getSessionTeam($roundWinner)];
			}else{
				$temp[$this->getSessionTeam($roundWinner)] = 1;
			}
		}
		arsort($temp);
		return key($temp);
	}
	public function getRoundsWon(ClassicSession $session){
		$wins = 0;
		foreach($this->roundWinners as $roundWinner){
			if($roundWinner === $session){
				$wins++;
			}
		}
		return $wins;
	}
	/**
	 * @param ClassicSession $session
	 * @return int
	 */
	public function getKills(ClassicSession $session){
		return $this->kills[$session->getPlayer()->getName()];
	}
	/**
	 * @param ClassicSession $session
	 * @param int $kills
	 */
	public function setKills(ClassicSession $session, $kills){
		$this->kills[$session->getPlayer()->getName()] = $kills;
	}
	/**
	 * @return int
	 */
	public function getTime(){
		return $this->time;
	}
	/**
	 * @param int $time
	 */
	public function setTime($time){
		$this->time = $time;
	}
	/**
	 * @return int
	 */
	public function getDuration(){
		return $this->roundDuration;
	}
	/**
	 * @return int
	 */
	public function getStatus(){
		return $this->status;
	}
	/**
	 * @param int $status
	 * @param string $message
	 * @param string $winner
	 */
	public function setStatus($status, $message = "", $winner = "no one"){
		switch($status){
			case self::STATUS_STARTING:
				++$this->currentRound;
				foreach($this->teams as $team => $sessions){
					foreach($sessions as $session){
						if($team === 0){
							$session->getPlayer()->teleport(new Vector3(212, 16, 22));
							$session->getPlayer()->setRotation(150, $session->getPlayer()->getPitch());
						}else{
							if($team === 1){
								$session->getPlayer()->teleport(new Vector3(200, 16, 3));
								$session->getPlayer()->setRotation(-30, $session->getPlayer()->getPitch());
							}
						}
						if($message !== ""){
							$session->getPlayer()->sendMessage($message);
						}
						$this->kit->apply($session);
					}
					$this->time = 5;
				}
				$this->canHit = false;
				break;
			case self::STATUS_RUNNING:
				foreach($this->getSessions() as $session){
					if($message !== ""){
						$session->getPlayer()->sendMessage($message);
					}
					$session->getPlayer()->sendMessage(TextFormat::GOLD . "Round " . TextFormat::RED . $this->getRound()."/".$this->getMaxRounds());
				}
				$this->time = $this->roundDuration;
				$this->canHit = true;
				break;
			case self::STATUS_ENDING:
				foreach($this->getSessions() as $session){
					if($session->getPlayer()->isOnline()){
						$this->old[$session->getPlayer()->getName()]->restore();
						$session->setBattle(null);
						if($message !== ""){
							$session->getPlayer()->sendMessage($message);
						}
						$coins = ($winner === $session->getPlayer()->getName() ? 48 : $this->getRoundsWon($session) * 6);
						$session->getPlayer()->sendMessage(TextFormat::GOLD . "You won " . TextFormat::RED . $this->getRoundsWon($session) . TextFormat::GOLD . " rounds and received " . TextFormat::RED . $coins . TextFormat::GOLD . " coins.");
						$session->grantCoins($coins, false, false);
						$session->getPlayer()->sendMessage(TextFormat::GOLD . "Winner: " . TextFormat::RED . $winner);
					}
					foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
						$player->showPlayer($session->getPlayer());
						$session->getPlayer()->showPlayer($player);
					}
				}
				$this->canHit = false;
				unset($this->plugin->battles[$this->id]);
				break;
		}
		$this->status = $status;
	}
	/**
	 * @return ClassicBattleKit
	 */
	public function getKit(){
		return $this->kit;
	}
	/**
	 * @return bool
	 */
	public function canHit(){
		return $this->canHit;
	}
	/**
	 * @return ClassicSession[]
	 */
	public function getSessions(){
		$out = [];
		foreach($this->teams as $team => $sessions){
			foreach($sessions as $session){
				$out[] = $session;
			}
		}
		return $out;
	}
	/**
	 * @param ClassicSession $session
	 * @return bool|int
	 */
	public function getSessionTeam(ClassicSession $session){
		$returnTeam = false;
		foreach($this->teams as $team => $sessions){
			foreach($sessions as $newSession){
				if($session === $newSession){
					$returnTeam = $team;
				}
			}
		}
		return $returnTeam;
	}
	/**
	 * @param string $message
	 * @param string $type
	 */
	public function broadcast($message, $type = "msg"){
		switch($type){
			case "msg":
				foreach($this->getSessions() as $session){
					$session->getPlayer()->sendMessage($message);
				}
				break;
			case "popup":
				foreach($this->getSessions() as $session){
					$session->getPlayer()->sendPopup($message);
				}
				break;
			case "tip":
				foreach($this->getSessions() as $session){
					$session->getPlayer()->sendTip($message);
				}
				break;
		}
	}

}
