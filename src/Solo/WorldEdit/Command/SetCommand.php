<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use Solo\WorldEdit\WorldEdit;

class SetCommand extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/set', '선택한 지점을 블럭으로 채웁니다.', '', ['/채우기', '/fill']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args):void
    {
        if(!$this->testPermission($sender)) return;
        if (!$sender instanceof Player) {
            $sender->sendMessage("§b§o[ 알림 ] §7인게임에서만 사용가능합니다.");
            return;
        }
        if (!WorldEdit::getInstance()->isAreaSelected($sender->getName())) {
            $sender->sendMessage("§b§o[ 알림 ] §7첫번째 지점과 두번째 지점을 선택해주세요.");
            return;
        } else {
            if (!isset($args[0])) {
                $sender->sendMessage("§b§o[ 알림 ] §7사용법 : //set [블럭 코드]");
                return;
            }
            $pos = WorldEdit::getInstance()->getPos($sender->getName());
            if (is_numeric(str_replace([':', ','], ['', ''], $args[0]))) {

                if (WorldEdit::getInstance()->getServer()->getWorldManager()->getWorldByName($pos['level1']) !== null)
                    $lv = WorldEdit::getInstance()->getServer()->getWorldManager()->getWorldByName($pos['level1']);
                else
                    $lv = $sender->getWorld();
                //Undo
                WorldEdit::getInstance()->writeUndo((int)$pos['x1'], (int)$pos['y1'], (int)$pos['z1'], (int)$pos['x2'], (int)$pos['y2'], (int)$pos['z2'], $lv, $sender);

                $ids = explode(',', $args[0]);
                WorldEdit::getInstance()->setBlockArea((int)$pos['x1'], (int)$pos['y1'], (int)$pos['z1'], (int)$pos['x2'], (int)$pos['y2'], (int)$pos['z2'], $lv, $ids, $sender);
                $sender->sendMessage("§b§o[ 알림 ] §7" . WorldEdit::getInstance()->calculateArea((int)$pos['x1'], (int)$pos['y1'], (int)$pos['z1'], (int)$pos['x2'], (int)$pos['y2'], (int)$pos['z2']) . "개의 블럭을 채웠습니다.");
            } else {
                $sender->sendMessage("§b§o[ 알림 ] §7사용법 : //set [블럭 코드]");
            }
        }
    }
}