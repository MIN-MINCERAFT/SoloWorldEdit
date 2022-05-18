<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use Solo\WorldEdit\WorldEdit;

class PasteCommand extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/paste', '선택한 지점을 붙여넣습니다.', '', ['/붙여넣기']);
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args):void
    {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;

        if (isset($args[0])) {
            $clipboard = $args[0];
            $i = $args[0] . " 클립보드가 존재하지 않습니다.";
        } else {
            $clipboard = $sender->getName();
            $i = "복사를 먼저 해주세요.";
        }
        if (!isset(WorldEdit::getInstance()->clipboard[$clipboard])) {
            $sender->sendMessage("§b§o[ 알림 ] §7" . $i);
            return;
        }
        $x = floor($sender->getPosition()->getX());
        $y = floor($sender->getPosition()->getY());
        $z = floor($sender->getPosition()->getZ());
        $level = $sender->getWorld();
        $s= $sender->getLocation()->getYaw();
        $direction = $this->getDirection($s);

        $pos = WorldEdit::getInstance()->getPastePos($x, $y, $z, $direction, $level, $clipboard);
        //Undo
        WorldEdit::getInstance()->writeUndo($pos['x1'], $pos['y1'], $pos['z1'], $pos['x2'], $pos['y2'], $pos['z2'], $level, $sender);

        WorldEdit::getInstance()->setBlockAreaByClipboard($x, $y, $z, $direction, $level, $sender, $clipboard);
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