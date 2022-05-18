<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class xyzCommand extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/xyz', '현재 좌표를 보여줍니다.', '', ['/좌표']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args):void
    {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;
        $x = floor($sender->getPosition()->getX());
        $y = floor($sender->getPosition()->getY());
        $z = floor($sender->getPosition()->getZ());
        $sender->sendMessage("§b§o[ 알림 ] §7현재 좌표 : " . $x . " . " . $y . " . " . $z);
    }
}