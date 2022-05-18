<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use Solo\WorldEdit\WorldEdit;

class ReplaceCommand extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/replace', '선택한 지점에서 블럭을 교체합니다.', '', ['/바꾸기','/변경','/교체']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args):void
    {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;
        if (!WorldEdit::getInstance()->isAreaSelected($sender->getName())) {
            $sender->sendMessage("§b§o[ 알림 ] §7첫번째 지점과 두번째 지점을 선택해주세요.");
            return;
        }
        if (!isset($args[1]) || !is_numeric(str_replace(':', '', $args[0])) || !is_numeric(str_replace(':', '', $args[1]))) {
            $sender->sendMessage("§b§o[ 알림 ] §7사용법 : //replace [대상 블럭] [교체할 블럭]");
        } else {
            $pos = WorldEdit::getInstance()->getPos($sender->getName());

            if (WorldEdit::getInstance()->getServer()->getWorldManager()->getWorldByName($pos['level1']) !== null)
                $lv = WorldEdit::getInstance()->getServer()->getWorldManager()->getWorldByName($pos['level1']);
            else $lv = $sender->getWorld();
            WorldEdit::getInstance()->writeUndo($pos['x1'], $pos['y1'], $pos['z1'], $pos['x2'], $pos['y2'], $pos['z2'], $lv, $sender);

            WorldEdit::getInstance()->replaceBlockArea($pos['x1'], $pos['y1'], $pos['z1'], $pos['x2'], $pos['y2'], $pos['z2'], $lv, $args[0], $args[1], $sender, true);
        }
    }
}