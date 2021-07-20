<?php

namespace refaltor\CustomItems\Events;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use refaltor\CustomItems\Loader\Load;
use refaltor\CustomItems\Main;

class OnData implements Listener
{
    public function onPacket(DataPacketSendEvent $event){
        $packet = $event->getPacket();
        if ($packet instanceof StartGamePacket) {
            $packet->itemTable = Load::$entries;
        }
    }

    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        foreach (Main::getInstance()->getConfig()->get('add') as $name => $keys){
            if ($item->getId() === $keys['id']){
                if ($keys['type'] === 'armor') {
                    $type = $keys['armor'];
                    switch (strtolower($type)){
                        case 'helmet':
                            if ($player->getArmorInventory()->getHelmet()->getId() !== Item::AIR){
                                $old = $player->getArmorInventory()->getHelmet();
                                $player->getInventory()->getItemInHand()->setCount($item->getCount() - 1);
                                $item = $item->setCount(1);
                                $player->getArmorInventory()->setHelmet($item);
                                if ($player->getInventory()->canAddItem($old)){
                                    $player->getInventory()->addItem($old);
                                }else $player->getLevel()->dropItem($player, $old);
                            }else $player->getArmorInventory()->setHelmet($item);
                            $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_ARMOR_EQUIP_CHAIN);
                            break;
                        case 'chestplate':
                            if ($player->getArmorInventory()->getChestplate()->getId() !== Item::AIR){
                                $old = $player->getArmorInventory()->getChestplate();
                                $player->getInventory()->getItemInHand()->setCount($item->getCount() - 1);
                                $item = $item->setCount(1);
                                $player->getArmorInventory()->setChestplate($item);
                                if ($player->getInventory()->canAddItem($old)){
                                    $player->getInventory()->addItem($old);
                                }else $player->getLevel()->dropItem($player, $old);
                            }else $player->getArmorInventory()->setChestplate($item);
                            $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_ARMOR_EQUIP_CHAIN);
                            break;
                        case 'leggings':
                            if ($player->getArmorInventory()->getLeggings()->getId() !== Item::AIR){
                                $old = $player->getArmorInventory()->getHelmet();
                                $player->getInventory()->getItemInHand()->setCount($item->getCount() - 1);
                                $item = $item->setCount(1);
                                $player->getArmorInventory()->setLeggings($item);
                                if ($player->getInventory()->canAddItem($old)){
                                    $player->getInventory()->addItem($old);
                                }else $player->getLevel()->dropItem($player, $old);
                            }else $player->getArmorInventory()->setLeggings($item);
                            $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_ARMOR_EQUIP_CHAIN);
                            break;
                        case 'boots':
                            if ($player->getArmorInventory()->getBoots()->getId() !== Item::AIR){
                                $old = $player->getArmorInventory()->getBoots();
                                $player->getInventory()->getItemInHand()->setCount($item->getCount() - 1);
                                $item = $item->setCount(1);
                                $player->getArmorInventory()->setBoots($item);
                                if ($player->getInventory()->canAddItem($old)){
                                    $player->getInventory()->addItem($old);
                                }else $player->getLevel()->dropItem($player, $old);
                            }else $player->getArmorInventory()->setBoots($item);
                            $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_ARMOR_EQUIP_CHAIN);
                            break;
                    }
                }
            }
        }
    }
}