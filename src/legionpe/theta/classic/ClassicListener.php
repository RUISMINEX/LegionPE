<?php

/*
 * LegionPE
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

use legionpe\theta\BaseListener;
use legionpe\theta\utils\CallbackPluginTask;
use pocketmine\block\Block;
use pocketmine\entity\Arrow;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\DisconnectPacket;
use pocketmine\network\protocol\InteractPacket;
use pocketmine\utils\TextFormat;

class ClassicListener extends BaseListener{
	public function onPacketReceive(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		if($packet instanceof InteractPacket){
			if($packet->action === 2){
				$ses = $this->getMain()->getSession($event->getPlayer());
				if($ses instanceof ClassicSession){
					if(isset($ses->kitStands[$packet->target])){
						$kit = $ses->kitStands[$packet->target]->getKit();
						if($kit->getLevel() <= $kit->id){
							$ses->currentKit = $kit;
							$ses->sendMessage(TextFormat::AQUA . "You have selected the kit " . TextFormat::GREEN . $kit->getName() . "\n" . TextFormat::AQUA . "You can change the level of the kit at the 'current kit' stand.");
							$ses->setLoginDatum("pvp_kit", $kit->id);
						}else{
							if($ses->getCoins() >= $kit->getPrice()){
								$ses->kitData[$kit->id] = $kit->getLevel();
								$ses->setCoins($ses->getCoins() - $kit->getPrice());
								$ses->kitStands[$packet->target]->update();
								$ses->sendMessage(TextFormat::AQUA . "You have unlocked level " . TextFormat::RED . $kit->getLevel() . TextFormat::AQUA . " (kit {$kit->getName()} \nTo use this kit (and this level), please go to the 'current kit' stand.");
							}
						}
					}
				}
			}
		}
	}
	public function onPacketSend(DataPacketSendEvent $event){
		$pk = $event->getPacket();
		if($pk instanceof DisconnectPacket){
			if($pk->message === "client disconnect"){
				$ses = $this->getMain()->getSession($event->getPlayer());
				if($ses instanceof ClassicSession){
					$ses->onClientDisconnect();
				}
			}
		}
	}
	/**
	 * @param ProjectileHitEvent $event
	 * @priority HIGH
	 */
	public function onProjectileHit(ProjectileHitEvent $event){
		if($event->getEntity() instanceof Arrow){
			$this->getMain()->getServer()->getScheduler()->scheduleDelayedTask(new CallbackPluginTask($this->getMain(), function (Arrow $arrow){
				if($arrow->isAlive()){
					$arrow->kill();
				}
			}, $event->getEntity()), 1);
		}
	}
	public function onBlockSpread(BlockSpreadEvent $event){
		// people placing lava in battles
		$block = $event->getSource();
		$event->getBlock()->getLevel()->setBlock(new Vector3($block->getX(), $block->getY(), $block->getZ()), Block::get(0));
		$event->setCancelled();
	}
	public function onExplosionPrime(ExplosionPrimeEvent $event){
		$event->setCancelled();
	}
}
