<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Solo\WorldEdit\WorldEdit;

class CopyCommand extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/copy', '선택한 지점을 복사합니다.', '', ['/복사']);
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args):void
    {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;
        $x = floor($sender->getPosition()->getX());
        $y = floor($sender->getPosition()->getY());
        $z = floor($sender->getPosition()->getZ());
        $s= $sender->getLocation()->getYaw();
        $direction = $this->getDirection($s);
        //0 : 남         2
        //1 : 서     1      3
        //2 : 북         0
        //3 : 동
        if (!WorldEdit::getInstance()->isAreaSelected($sender->getName())) {
            $sender->sendMessage("§b§o[ 알림 ] §7첫번째 지점과 두번째 지점을 선택해주세요.");
            return;
        } else {
            if (isset($args[0])) {
                $clipboard = $args[0];
                $i = $args[0] . " 클립보드에 저장하였습니다.";
            } else {
                $clipboard = $sender->getName();
                $i = "복사하였습니다.";
            }
            $pos = WorldEdit::getInstance()->getPos($sender->getName());

            if (WorldEdit::getInstance()->getServer()->getWorldManager()->getWorldByName($pos['level1']) !== null)
                $lv = WorldEdit::getInstance()->getServer()->getWorldManager()->getWorldByName($pos['level1']);
            else
                $lv = $sender->getWorld();

            WorldEdit::getInstance()->getBlockArea((int)$pos['x1'], (int)$pos['y1'], (int)$pos['z1'], (int)$pos['x2'], (int)$pos['y2'], (int)$pos['z2'], (int)$x, (int)$y, (int)$z, $direction, $lv, $sender, $clipboard);
            $sender->sendMessage("§b§o[ 알림 ] §7" . count(WorldEdit::getInstance()->clipboard[$clipboard]['block']) . "개의 블럭을 " . $i);
        }
    }
    public function getDirection($y) : ?int{
        $rotation = fmod( $y- 90, 360);
        if($rotation < 0){
            $rotation += 360.0;
        }
        if((0 <= $rotation and $rotation < 45) or (315 <= $rotation and $rotation < 360)){
            return 2; //North
        }elseif(45 <= $rotation and $rotation < 135){
            return 3; //East
        }elseif(135 <= $rotation and $rotation < 225){
            return 0; //South
        }elseif(225 <= $rotation and $rotation < 315){
            return 1; //West
        }else{
            return null;
        }
    }
}