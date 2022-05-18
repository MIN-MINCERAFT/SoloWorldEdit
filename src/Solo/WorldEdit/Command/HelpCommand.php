<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class HelpCommand extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/', '월드 에딧 도움말을 표시합니다', '');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args):void
    {
        if(!$this->testPermission($sender)) return;
        if (isset($args[0])) {
            if ($args[0] == "2") {
                $sender->sendMessage("§b§o[ 알림 ] §7월드 에딧 도움말을 표시합니다. (2페이지)");
                $sender->sendMessage("§b§o[ 알림 ] §7//copy - 선택한 지점을 복사합니다.");
                $sender->sendMessage("§b§o[ 알림 ] §7//paste - 복사한 블럭을 붙여넣기 합니다.");
                $sender->sendMessage("§b§o[ 알림 ] §7//undo - 마지막으로 했던 작업을 취소합니다. (도구 제외)");
                $sender->sendMessage("§b§o[ 알림 ] §7//클립보드 - 클립보드 관련 명령어입니다.");
                $sender->sendMessage("§b§o[ 알림 ] §7//도구 - 도구 관련 명령어입니다.");
                return;
            }
        }
        $sender->sendMessage("§b§o[ 알림 ] §7월드 에딧 도움말을 표시합니다. (1페이지)");
        $sender->sendMessage("§b§o[ 알림 ] §7//1 - 첫번째 지점을 선택합니다.");
        $sender->sendMessage("§b§o[ 알림 ] §7//2 - 두번째 지점을 선택합니다.");
        $sender->sendMessage("§b§o[ 알림 ] §7//set [블럭 코드] - 선택한 지점을 블럭으로 채웁니다.");
        $sender->sendMessage("§b§o[ 알림 ] §7//replace [블럭 코드] [블럭 코드] - 선택한 지점에서 블럭을 교체합니다.");
        $sender->sendMessage("§b§o[ 알림 ] §7//cut - 선택한 지점을 잘라냅니다.");
    }
}