<?php

declare(strict_types=1);

namespace xPrim69x\ATEnchants;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class EnchantCommand extends Command{

	const RARITY_COLORS = [
		Enchantment::RARITY_COMMON => TF::GREEN,
		Enchantment::RARITY_UNCOMMON => TF::DARK_GREEN,
		Enchantment::RARITY_RARE => TF::YELLOW,
		Enchantment::RARITY_MYTHIC => TF::GOLD
	];

	const SWORD_ENCHANTS = [
		"Kaboom" => 3,
		"Zeus" => 3,
		"Bleed" => 3,
		"Daze" => 3,
		"Frost" => 3,
		"Hades" => 3,
		"Poison" => 2,
		"Lifesteal" => 2,
		"Uplift" => 1,
		"OOF" => 1
	];

	const ARMOR_ENCHANTS = [
		"Bunny" => 3,
		"Gears" => 2,
		"Overlord" => 2,
		"Glowing" => 1,
		"Scorch" => 5,
		"Adrenaline" => 1,
	];

	const BOW_ENCHANTS = [
		"Relocate" => 1
	];

	const PICKAXE_ENCHANTS = [
		"Feed" => 1
        "Explosion" => 3
	];

	public function __construct(){
		parent::__construct(
			"at",
			TF::AQUA . "Enchants!",
			TF::GRAY . "Usage: " . TF::RED . "/at enchant <player> <enchantment> [level]"
		);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player){
			$sender->sendMessage(TF::DARK_RED . "Please use this command in-game!");
			return;
		}
		if(count($args) < 1){
			$sender->sendMessage(TF::GRAY . "Usage: " . TF::RED . "/at <list:enchant>");
			return;
		}
		if($args[0] == "list") {
			$sender->sendMessage(TF::RED . "Sword Enchants:");
			foreach(self::SWORD_ENCHANTS as $ench => $level){
				$sender->sendMessage(TF::GOLD . "$ench ($level)");
			}
			$sender->sendMessage(TF::EOL . TF::RED . "Armor Enchants:");
				foreach(self::ARMOR_ENCHANTS as $ench => $level){
					$sender->sendMessage(TF::GOLD . "$ench ($level)");
			}
			$sender->sendMessage(TF::EOL . TF::RED . "Bow Enchants:");
			foreach(self::BOW_ENCHANTS as $ench => $level){
				$sender->sendMessage(TF::GOLD . "$ench ($level)");
			}
			$sender->sendMessage(TF::EOL . TF::RED . "Pickaxe Enchants:");
			foreach(self::PICKAXE_ENCHANTS as $ench => $level){
				$sender->sendMessage(TF::GOLD . "$ench ($level)");
			}
			return;
		}
		if($args[0] == "enchant"){
			if(!$sender->hasPermission("customenchant.command")) {
				$sender->sendMessage(TF::DARK_RED . "You do not have permission to use this command!");
				return;
			}

			if(count($args) < 3){
				$sender->sendMessage($this->usageMessage);
				return;
			}

			$player = $sender->getServer()->getPlayer($args[1]);

			if($player === null){
				$sender->sendMessage(TF::RED . "That player is not online!");
				return;
			}

			$item = $player->getInventory()->getItemInHand();

			if ($item->isNull()){
				$sender->sendMessage(TF::RED . "You have to be holding an item to enchant!");
				return;
			}

			if (is_numeric($args[2])){
				$enchantment = Enchantment::getEnchantment((int)$args[2]);
			} elseif (isset(CustomEnchantManager::CONVERSIONS[strtolower($args[2])])) {
				$enchantment = Enchantment::getEnchantment(CustomEnchantManager::CONVERSIONS[strtolower($args[2])]);
			} else {
				$sender->sendMessage(TF::RED . $args[2] . " is not a valid enchant!");
				return;
			}

			if(is_numeric($args[2])){
				if($args[2] < 69){
					$sender->sendMessage(TF::RED . "That enchantment is not a custom enchant.");
					return;
				}
			}

			if (!($enchantment instanceof Enchantment) && is_numeric($args[2])) {
				$sender->sendMessage(TF::RED . $args[2] . " is not a valid enchant!");
				return;
			}
			if(!Main::canEnchant($item,$enchantment)){
				$name = $enchantment->getName();
				$sender->sendMessage(TF::RED . "$name is not compatible with this item!");
				return;
			}

			$level = 1;
			if (isset($args[3])) {
				$max = $enchantment->getMaxLevel();
				$level = (int)$args[3];
				if ($level > $max) {
					$sender->sendMessage(TF::RED . "That level is too high! The most it can be is $max");
					return;
				}
				if ($level < 1) {
					$sender->sendMessage(TF::RED . "That level is too low! The least it can be is 1");
					return;
				}
				if ($level === null) {
					return;
				}
			}

			$item->addEnchantment(new EnchantmentInstance($enchantment, $level));
			//Lores also by BoomYourBang thanks :)
			$lores = [];
			$enchants = array_filter($item->getEnchantments(), function ($enchantment) {
				return $enchantment->getId() > 36;
			});

			foreach ($enchants as $enchantment) {
				$lores[] = TF::RESET . self::RARITY_COLORS[$enchantment->getType()->getRarity()] . $enchantment->getType()->getName() . " " . Main::lvlToRomanNum($enchantment->getLevel());
			}
			#asort($lores); //order rarity
			$item->setLore($lores);

			$player->getInventory()->setItemInHand($item);
			$sender->sendMessage(TF::AQUA . "Enchanting succeeded for " . $player->getName());
		} else {
			$sender->sendMessage(TF::GRAY . "Usage: " . TF::RED . "/at <list:enchant>");
		}
	}
}
