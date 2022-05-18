<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use Solo\WorldEdit\WorldEdit;

class Pos1Command extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/pos1', '첫번째 지점을 선택합니다.', '', ['/1']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args):void
    {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;
        $x = floor($sender->getPosition()->getX());
        $y = floor($sender->getPosition()->getY());
        $z = floor($sender->getPosition()->getZ());
        WorldEdit::getInstance()->setPos1($x, $y, $z, $sender);
    }
}