<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Solo\WorldEdit\WorldEdit;

class ClipboardCommand extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/클립보드', '클립보드 관련 명령어입니다.', '');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args):void
    {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;
        $api = WorldEdit::getInstance();
        if (!isset($args[0])) {
            $sender->sendMessage("§b§o[ 알림 ] §7====== 클립보드 명령어 ======");
            $sender->sendMessage("§b§o[ 알림 ] §7//클립보드 목록 - 클립보드 목록을 봅니다.");
            $sender->sendMessage("§b§o[ 알림 ] §7//클립보드 삭제 [클립보드 이름] - 해당 클립보드를 삭제합니다.");
            $sender->sendMessage("§b§o[ 알림 ] §7//클립보드 저장 [클립보드 이름] - 해당 클립보드를 저장합니다.");
            $sender->sendMessage("§b§o[ 알림 ] §7//클립보드 불러오기 [클립보드 이름] - 플러그인 데이터 폴더로부터 클립보드를 불러옵니다.");
            return;
        }
        switch ($args[0]) {
            case "저장":
                if (!isset($args[1])) {
                    $sender->sendMessage("§b§o[ 알림 ] §7사용법 : /클립보드 저장 [클립보드 이름]");
                    return;
                }
                if (!isset($api->clipboard[$args[1]])) {
                    $sender->sendMessage("§b§o[ 알림 ] §7해당 클립보드는 존재하지 않습니다.");
                    return;
                }
                $array = $api->clipboard[$args[1]];
                $api->exportConfig($array, $args[1]);
                $sender->sendMessage("§b§o[ 알림 ] §7클립보드가 저장되었습니다.");
                return;
            case "목록":
                $sender->sendMessage("§b§o[ 알림 ] §7====== 클립보드 목록 ======");
                foreach (array_keys($api->clipboard) as $key) {
                    $info = explode('-', $key);
                    if (count($info) == 3)
                        if ($info[0] == "UNDO")
                            continue;
                    $mount = count($api->clipboard[$key]['block']);
                    $sender->sendMessage("§b§o[ " . $key . " ] §7블럭 갯수 : " . $mount . "개");
                }
                return;
            case "삭제":
                if (!isset($args[1])) {
                    $sender->sendMessage("§b§o[ 알림 ] §7사용법 : /클립보드 삭제 [클립보드 이름]");
                    return;
                }
                if (!isset($api->clipboard[$args[1]])) {
                    $sender->sendMessage("§b§o[ 알림 ] §7해당 클립보드는 존재하지 않습니다.");
                    return;
                }
                unset($api->clipboard[$args[1]]);
                $sender->sendMessage("§b§o[ 알림 ] §7성공적으로 클립보드를 삭제하였습니다.");
                return;
            case "불러오기":
                if (!isset($args[1])) {
                    $sender->sendMessage("§b§o[ 알림 ] §7사용법 : /클립보드 불러오기 [클립보드 이름]");
                    return;
                }
                if (!$api->isExistConfig($args[1])) {
                    $sender->sendMessage("§b§o[ 알림 ] §7해당 클립보드는 존재하지 않습니다.");
                    return;
                }
                $api->clipboard[$args[1]] = $api->importConfig($args[1]);
                $sender->sendMessage("§b§o[ 알림 ] §7성공적으로 클립보드를 불러왔습니다.");
                return;
        }
    }
}