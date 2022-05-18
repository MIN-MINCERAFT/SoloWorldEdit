<?php

declare(strict_types=1);

namespace Solo\WorldEdit\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Solo\WorldEdit\WorldEdit;

class UndoCommand extends Command
{
    public function __construct()
    {
        $this->setPermission('world.op');
        parent::__construct('/undo', '마지막으로 했던 작업을 취소합니다. (도구 제외).', '', ['/되돌리기']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args):void
    {
        if (!$sender instanceof Player) return;
        if (!$this->testPermission($sender)) return;
        $api = WorldEdit::getInstance();
        if (isset($args[0]) && $args[0] == "목록") {
            $target = $args[1] ?? strtolower($sender->getName());
            $array = [];
            foreach (array_keys($api->clipboard) as $key) {
                $info = explode('-', $key);
                if ($info[0] == "UNDO")
                    if ($info[1] == $target)
                        $array[] = $key;
            }
            $sender->sendMessage("§b§o[ 알림 ] §7" . $target . "의 작업 기록을 불러옵니다.");
            foreach ($array as $k) {
                $date = $api->clipboard[$k]['date'];
                $block = count($api->clipboard[$k]['block']);
                $sender->sendMessage("§b§o[ " . $date . " ] §7블럭 수 : " . $block);
            }
            $sender->sendMessage("§b§o[ 알림 ] §7======================");
            return;
        }
        if (isset($args[0])) {
            foreach (array_keys($api->clipboard) as $key) {
                $info = explode('-', $key);
                if ($info[0] == "UNDO")
                    if ($info[1] == strtolower($sender->getName())) {
                        $api->executeUndo($api->getServer()->getPlayerByPrefix(strtolower($args[0])));
                        $sender->sendMessage("§b§o[ 알림 ] §7성공적으로 " . $args[0] . " 플레이어의 작업을 되돌렸습니다.");
                        return;
                    }
            }
            $sender->sendMessage("§b§o[ 알림 ] §7해당 플레이어는 작업 기록이 없습니다.");
        }
        foreach (array_keys($api->clipboard) as $key) {
            $info = explode('-', $key);
            if ($info[0] == "UNDO")
                if ($info[1] == strtolower($sender->getName())) {
                    $api->executeUndo($sender);
                    $sender->sendMessage("§b§o[ 알림 ] §7성공적으로 되돌렸습니다.");
                    return;
                }
        }
        $sender->sendMessage("§b§o[ 알림 ] §7이전 작업 기록이 없습니다.");
    }
}