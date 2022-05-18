<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Solo\WorldEdit\WorldEdit;

class ToolCommand extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/tool', '도구 명령어입니다.', '', ['/도구']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;
        $api = WorldEdit::getInstance();
        if (!isset($args[0])) {
            $sender->sendMessage("§b§o[ 알림 ] §7====== 월드 에딧 도구 ======");
            $sender->sendMessage("§b§o[ 알림 ] §7//도구 물제거 - 지정된 범위에서 물을 제거합니다.");
            $sender->sendMessage("§b§o[ 알림 ] §7//도구 용암제거 - 지정된 범위에서 용암을 제거합니다.");
            return;
        }
        if (!$api->isAreaSelected($sender->getName())) {
            $sender->sendMessage("§b§o[ 알림 ] §7첫번째 지점과 두번째 지점을 선택해주세요.");
            return;
        }
        $pos = $api->getPos($sender->getName());
        $i = match ($args[0]) {
            "물제거" => 8,
            "용암제거" => 10,
            default => 1
        };
        if($i === 8 or $i === 10)
        {
            $api->replaceBlockArea((int)$pos['x1'], (int)$pos['y1'], (int)$pos['z1'], (int)$pos['x2'], (int)$pos['y2'], (int)$pos['z2'], $sender->getWorld(), $i, 0, $sender, false);
        }else{
            $sender->sendMessage("§b§o[ 알림 ] §7====== 월드 에딧 도구 ======");
            $sender->sendMessage("§b§o[ 알림 ] §7//도구 물제거 - 지정된 범위에서 물을 제거합니다.");
            $sender->sendMessage("§b§o[ 알림 ] §7//도구 용암제거 - 지정된 범위에서 용암을 제거합니다.");
        }

    }
}