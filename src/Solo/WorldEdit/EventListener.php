<?php
declare(strict_types=1);

namespace Solo\WorldEdit;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemIds;

class EventListener implements Listener
{
	public function onInteract(PlayerInteractEvent $ev): void
	{
		$player = $ev->getPlayer();
		$id = $ev->getItem()->getId();
		$block = $ev->getBlock()->getPosition();
		$bd = $ev->getBlock()->getId();
		if ($id === ItemIds::WOODEN_AXE and $bd !== 0 and $player->isCreative()) {
			if (!$player->hasPermission('world.op')) return;
			if ($ev->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
			WorldEdit::getInstance()->setPos2($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), $ev->getPlayer());
		}
	}
	
	public function onBreak(BlockBreakEvent $ev): void
	{
		$player = $ev->getPlayer();
		$id = $ev->getItem()->getId();
		$block = $ev->getBlock()->getPosition();
		if ($id === ItemIds::WOODEN_AXE and $player->isCreative()) {
			if (!$player->hasPermission('world.op')) return;
			WorldEdit::getInstance()->setPos1($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), $ev->getPlayer());
			$ev->cancel();
		}
	}
	
}