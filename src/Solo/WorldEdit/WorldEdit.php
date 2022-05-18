<?php

declare(strict_types=1);

namespace Solo\WorldEdit;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\tile\Sign;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use Solo\WorldEdit\Command\ClipboardCommand;
use Solo\WorldEdit\Command\CopyCommand;
use Solo\WorldEdit\Command\CUtCommand;
use Solo\WorldEdit\Command\HelpCommand;
use Solo\WorldEdit\Command\PasteCommand;
use Solo\WorldEdit\Command\Pos1Command;
use Solo\WorldEdit\Command\Pos2Command;
use Solo\WorldEdit\Command\ReplaceCommand;
use Solo\WorldEdit\Command\SetCommand;
use Solo\WorldEdit\Command\ToolCommand;
use Solo\WorldEdit\Command\UndoCommand;
use Solo\WorldEdit\Command\xyzCommand;
use Solo\WorldEdit\trait\ArrayListTrait;
use Solo\WorldEdit\trait\RotationTrait;

class WorldEdit extends PluginBase
{
	use ArrayListTrait, RotationTrait, SingletonTrait;
	
	public static string $prefix = '§b§o[ 알림 ] §7';
	
	public array $list = [];
	
	public array $clipboard = [];
	
	protected function onLoad(): void
    {
        self::setInstance($this);
    }

    public function onEnable(): void
	{
        $this->getServer()->getCommandMap()->registerAll('world', [
            new SetCommand(),
            new CopyCommand(),
            new HelpCommand(),
            new PasteCommand(),
            new Pos1Command(),
            new Pos2Command(),
            new ReplaceCommand(),
            new UndoCommand(),
            new CUtCommand(),
            new ClipboardCommand(),
            new xyzCommand(),
            new ToolCommand()
        ]);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}
	
	public function isExistConfig($config): bool
	{
		if (file_exists($this->getDataFolder() . "WorldEdit_" . $config . ".json")) return true;
		else return false;
	}
	
	public function importConfig($config): array
	{
		return JsonData::call($this->getDataFolder() . "WorldEdit_" . $config . '.json');
	}
	
	public function exportConfig($array, $config): void
	{
        JsonData::save($this->getDataFolder() . "WorldEdit_" . $config . '.json', $array);
	}
	
	public function getPos($player): array
	{
		$info1 = explode('/', $this->list[$player]['pos1']);
		$pos1 = explode('.', $info1[0]);
		$info2 = explode('/', $this->list[$player]['pos2']);
		$pos2 = explode('.', $info2[0]);
		return [
			'x1' => $pos1[0],
			'y1' => $pos1[1],
			'z1' => $pos1[2],
			'level1' => $info1[1],
			'x2' => $pos2[0],
			'y2' => $pos2[1],
			'z2' => $pos2[2],
			'level2' => $info2[1]
		];
	}
	
	public function calculateArea($x1, $y1, $z1, $x2, $y2, $z2): float|int
	{
		$x = (abs($x1 - $x2) + 1);
		$y = (abs($y1 - $y2) + 1);
		$z = (abs($z1 - $z2) + 1);
		return ($x * $y * $z);
	}
	
	public function isAreaSelected($player): bool
	{
		if (isset($this->list[$player]['pos1']) and isset($this->list[$player]['pos2'])) {
			$pos = $this->getPos($player);
			if ($pos['level1'] == $pos['level2']) return true;
			else return false;
		} else return false;
	}
	
	public function issetClipboard($clipboard): bool
	{
		if (!isset($this->clipboard[$clipboard]) or !isset($this->clipboard[$clipboard]['x']) or !isset($this->clipboard[$clipboard]['y']) or !isset($this->clipboard[$clipboard]['z'])) return false;
		return true;
	}
	
	public function isAfter($id): bool
	{
		if (isset($this->after[(string)$id])) return true;
		else return false;
	}
	
	public function isDependency($id): bool
	{
		if (isset($this->dependency[(string)$id])) return true;
		else return false;
	}
	
	public function CreateSign($text, $x, $y, $z, World $level): Tile|Entity|null
	{
		$nbt = CompoundTag::create()
			->setString('id', 'Sign')
			->setInt('x', $x)->setInt('y', $y)->setString('z', $z)
			->setString('Text1', $text[0])->setString('Text2', $text[1])
			->setString('Text3', $text[2])->setString('Text4', $text[3]);
		return TileFactory::getInstance()->createFromData($level, $nbt);
	}
	
	public function makeProgressBar($current, $max, $color, $sidecolor, $basecolor, $barsize = 30): string
	{
		$strbar = "";
		if ($current == $max) {
			for ($i = 1; $i <= $barsize; $i++)
				$strbar .= "=";
			return $sidecolor . "[" . $color . $strbar . $sidecolor . "] " . $current . "/" . $max;
		} else if ($current <= 0) {
			for ($i = 1; $i <= $barsize; $i++)
				$strbar .= "=";
			return $sidecolor . "[" . $basecolor . $strbar . $sidecolor . "] " . $current . "/" . $max;
		} else {
			$bar = floor($max / $barsize);
			($bar == 0) ? $bar = 1 : $bar = ceil($current / $bar);
			for ($i = 1; $i <= $bar; $i++)
				$strbar .= "=";
			$strbar .= $basecolor;
			$c = ($barsize - $bar);
			for ($i = 1; $i <= $c; ++$i)
				$strbar .= "=";
			return $sidecolor . "[" . $strbar . $sidecolor . "] " . $current . "/" . $max;
		}
	}
	
	public function setBlockArea(int $x1, int $y1, int $z1, int  $x2, int $y2, int $z2, World $level, $id, Player $player)
	{
		
		$pos1 = [];
		$pos2 = [];
		
		if ($x1 > $x2) {
			$pos1[0] = $x2;
			$pos2[0] = $x1;
		} else if ($x1 < $x2) {
			$pos1[0] = $x1;
			$pos2[0] = $x2;
		} else {
			$pos1[0] = $x1;
			$pos2[0] = $x1;
		}
		
		if ($y1 > $y2) {
			$pos1[1] = $y2;
			$pos2[1] = $y1;
		} else if ($y1 < $y2) {
			$pos1[1] = $y1;
			$pos2[1] = $y2;
		} else {
			$pos1[1] = $y1;
			$pos2[1] = $y1;
		}
		
		if ($z1 > $z2) {
			$pos1[2] = $z2;
			$pos2[2] = $z1;
		} else if ($z1 < $z2) {
			$pos1[2] = $z1;
			$pos2[2] = $z2;
		} else {
			$pos1[2] = $z1;
			$pos2[2] = $z1;
		}
		
		$block = [];
		if (is_array($id)) {
			foreach ($id as $i) {
				$i = explode(':', $i);
				if (count($i) == 1)
					array_push($block, BlockFactory::getInstance()->get((int)$i[0], 0));
				else if (count($i) == 2)
					array_push($block, BlockFactory::getInstance()->get((int)$i[0], (int)$i[1]));
			}
		} else {
			$i = explode(':', $id);
			if (count($i) == 1)
				array_push($block, BlockFactory::getInstance()->get((int)$i[0], 0));
			else if (count($i) == 2)
				array_push($block, BlockFactory::getInstance()->get((int)$i[0], (int)$i[1]));
			else
				return;
		}
		
		$count = 0;
		$max = $this->calculateArea($pos1[0], $pos1[1], $pos1[2], $pos2[0], $pos2[1], $pos2[2]);
		$microt = microtime(true);
		
		if (count($block) == 1)
			for ($x = $pos1[0]; $x <= $pos2[0]; $x++)
				for ($y = $pos1[1]; $y <= $pos2[1]; $y++)
					for ($z = $pos1[2]; $z <= $pos2[2]; $z++) {
						++$count;
						if ((microtime(true) - $microt) > 0.25 || $count == 0 || $max == $count) {
							$player->sendPopup($this->makeProgressBar($count, $max, '§a', '§a', '§0'));
							$microt = microtime(true);
						}
						$level->setBlock(new Vector3($x, $y, $z), $block[0], false);
					}
		else if (count($block) > 1) {
			$endid = (count($block) - 1);
			for ($x = $pos1[0]; $x <= $pos2[0]; $x++)
				for ($y = $pos1[1]; $y <= $pos2[1]; $y++)
					for ($z = $pos1[2]; $z <= $pos2[2]; $z++) {
						++$count;
						if ((microtime(true) - $microt) > 0.25 || $count == 0 || $max == $count) {
							$player->sendPopup($this->makeProgressBar($count, $max, '§a', '§a', '§0'));
							$microt = microtime(true);
						}
						$select = $block[mt_rand(0, $endid)];
						$level->setBlock(new Vector3($x, $y, $z), $select, false);
					}
		}
	}
	
	public function getPastePos($x1, $y1, $z1, $direction, World $level, $clipboard): bool|array
	{
		
		$pos = [];
		
		$xlength = $this->clipboard[$clipboard]['x'];
		$ylength = $this->clipboard[$clipboard]['y'];
		$zlength = $this->clipboard[$clipboard]['z'];
		$d = $this->getRotation($direction, $this->clipboard[$clipboard]['direction']);
		if ($d == 0) {
			$x1 = ($x1 + $this->clipboard[$clipboard]['playerxpos']);
			$y1 = ($y1 + $this->clipboard[$clipboard]['playerypos']);
			$z1 = ($z1 + $this->clipboard[$clipboard]['playerzpos']);
			$startX = $x1;
			$endX = ($x1 + $xlength - 1);
			$startY = $y1;
			$endY = ($y1 + $ylength - 1);
			$startZ = $z1;
			$endZ = ($z1 + $zlength - 1);
		} else if ($d == 1) {
			$x1 = ($x1 - $this->clipboard[$clipboard]['playerzpos']);
			$y1 = ($y1 + $this->clipboard[$clipboard]['playerypos']);
			$z1 = ($z1 + $this->clipboard[$clipboard]['playerxpos']);
			$startX = $x1;
			$endX = ($x1 - $zlength + 1);
			$startY = $y1;
			$endY = ($y1 + $ylength - 1);
			$startZ = $z1;
			$endZ = ($z1 + $xlength - 1);
		} else if ($d == 2) {
			$x1 = ($x1 - $this->clipboard[$clipboard]['playerxpos']);
			$y1 = ($y1 + $this->clipboard[$clipboard]['playerypos']);
			$z1 = ($z1 - $this->clipboard[$clipboard]['playerzpos']);
			$startX = $x1;
			$endX = ($x1 - $xlength + 1);
			$startY = $y1;
			$endY = ($y1 + $ylength - 1);
			$startZ = $z1;
			$endZ = ($z1 - $zlength + 1);
		} else if ($d == 3) {
			$x1 = ($x1 + $this->clipboard[$clipboard]['playerzpos']);
			$y1 = ($y1 + $this->clipboard[$clipboard]['playerypos']);
			$z1 = ($z1 - $this->clipboard[$clipboard]['playerxpos']);
			$startX = $x1;
			$endX = ($x1 + $zlength - 1);
			$startY = $y1;
			$endY = ($y1 + $ylength - 1);
			$startZ = $z1;
			$endZ = ($z1 - $xlength + 1);
		} else return false;
		
		$pos['x1'] = $startX;
		$pos['y1'] = $startY;
		$pos['z1'] = $startZ;
		$pos['x2'] = $endX;
		$pos['y2'] = $endY;
		$pos['z2'] = $endZ;
		return $pos;
	}
	
	public function replaceBlockArea($x1, $y1, $z1, $x2, $y2, $z2, World $level, $id1, $id2, Player $player, $dmgcheck)
	{
		$prefix = self::$prefix;
		
		$pos1 = [];
		$pos2 = [];
		
		if ($x1 > $x2) {
			$pos1[0] = (int)$x2;
			$pos2[0] = (int)$x1;
		} else if ($x1 < $x2) {
			$pos1[0] = (int)$x1;
			$pos2[0] = (int)$x2;
		} else {
			$pos1[0] = (int)$x1;
			$pos2[0] = (int)$x1;
		}
		
		if ($y1 > $y2) {
			$pos1[1] = (int)$y2;
			$pos2[1] = (int)$y1;
		} else if ($y1 < $y2) {
			$pos1[1] = (int)$y1;
			$pos2[1] = (int)$y2;
		} else {
			$pos1[1] = (int)$y1;
			$pos2[1] = (int)$y1;
		}
		
		if ($z1 > $z2) {
			$pos1[2] = (int)$z2;
			$pos2[2] = (int)$z1;
		} else if ($z1 < $z2) {
			$pos1[2] = (int)$z1;
			$pos2[2] = (int)$z2;
		} else {
			$pos1[2] = (int)$z1;
			$pos2[2] = (int)$z1;
		}
		
		$id1 = explode(':', (string)$id1);
		if (count($id1) == 1)
			$id1[1] = 0;
		
		$id2 = explode(':', (string)$id2);
		if (count($id2) == 1)
			$block2 = BlockFactory::getInstance()->get((int)$id2[0], 0);
		else if (count($id2) == 2)
			$block2 = BlockFactory::getInstance()->get((int)$id2[0],(int) $id2[1]);
		else
			return;
		
		$count = 0;
		$result = 0;
		$max = $this->calculateArea($pos1[0], $pos1[1], $pos1[2], $pos2[0], $pos2[1], $pos2[2]);
		
		$microt = microtime(true);
		
		if ($dmgcheck) {
			for ($x = $pos1[0]; $x <= $pos2[0]; $x++) {
				for ($y = $pos1[1]; $y <= $pos2[1]; $y++) {
					for ($z = $pos1[2]; $z <= $pos2[2]; $z++) {
						$count++;
						if ((microtime(true) - $microt) > 0.25 || $count == 0 || $max == $count) {
							$player->sendPopup($this->makeProgressBar($count, $max, '§a', '§a', '§0'));
							$microt = microtime(true);
						}
						if ($level->getBlockAt($x, $y, $z)->getId() == $id1[0]) {
							if ($level->getBlockAt($x, $y, $z)->getMeta() == $id1[1]) {
								$level->setBlock(new Vector3($x, $y, $z), $block2, false);
								$result++;
							}
						}
					}
				}
			}
		} else {
			for ($x = $pos1[0]; $x <= $pos2[0]; $x++) {
				for ($y = $pos1[1]; $y <= $pos2[1]; $y++) {
					for ($z = $pos1[2]; $z <= $pos2[2]; $z++) {
						$count++;
						if ((microtime(true) - $microt) > 0.25 || $count == 0 || $max == $count) {
							$player->sendPopup($this->makeProgressBar($count, $max, '§a', '§a', '§0'));
							$microt = microtime(true);
						}
						if ($level->getBlockAt($x, $y, $z)->getId() == (int)$id1[0]) {
							$level->setBlock(new Vector3($x, $y, $z), $block2, false);
							$result++;
						}
					}
				}
			}
		}
		$player->sendMessage("{$prefix}{$max}개의 블럭 중 {$result}개의 블럭이 교체되었습니다.");
	}
	
	public function setPos1($x, $y, $z, Player $player): void
	{
		$prefix = self::$prefix;
		$level = $player->getWorld()->getFolderName();
		if ($y > 256) $y = 256;
		if ($y < 0) $y = 0;
		$this->list[$player->getName()]['pos1'] = $x . '.' . $y . '.' . $z . '/' . $level;
		if (isset($this->list[$player->getName()]['pos2'])) {
			$info = explode('/', $this->list[$player->getName()]['pos2']);
			if ($info[1] !== $level) {
				unset($this->list[$player->getName()]['pos2']);
				$player->sendMessage("{$prefix}첫번째 좌표를 지정하였습니다. ( $x , $y , $z )");
				return;
			}
			$player->sendMessage("{$prefix}첫번째 좌표를 지정하였습니다. ( $x , $y , $z )");
			$pos = explode('.', $info[0]);
			$area = $this->calculateArea($x, $y, $z, $pos[0], $pos[1], $pos[2]);
			$player->sendMessage("{$prefix}선택된 영역의 블럭 갯수 : {$area}개");
		} else {
			$player->sendMessage("{$prefix}첫번째 좌표를 지정하였습니다. ( $x , $y , $z )");
		}
	}
	
	public function setPos2($x, $y, $z, Player $player): void
	{
		$prefix = self::$prefix;
		$level = $player->getWorld()->getFolderName();
		if ($y > 256) $y = 256;
		if ($y < 0) $y = 0;
		$this->list[$player->getName()]['pos2'] = $x . '.' . $y . '.' . $z . '/' . $level;
		if (isset($this->list[$player->getName()]['pos1'])) {
			$info = explode('/', $this->list[$player->getName()]['pos1']);
			if ($info[1] !== $level) {
				unset($this->list[$player->getName()]['pos1']);
				$player->sendMessage("{$prefix}두번째 좌표를 지정하였습니다. ( $x , $y , $z )");
				return;
			}
			$player->sendMessage("{$prefix}두번째 좌표를 지정하였습니다. ( $x , $y , $z )");
			$pos = explode('.', $info[0]);
			$area = $this->calculateArea($x, $y, $z, $pos[0], $pos[1], $pos[2]);
			$player->sendMessage("{$prefix}선택된 영역의 블럭 갯수 : {$area}개");
		} else {
			$player->sendMessage("{$prefix}두번째 좌표를 지정하였습니다. ( $x , $y , $z )");
		}
	}

    public function writeUndo($x1, $y1, $z1, $x2, $y2, $z2, World $level, Player $player)
    {
        $count = 0;
        foreach (array_keys($this->clipboard) as $key) {
            $info = explode('-', $key);
            if ($info[0] == "UNDO")
                if ($info[1] == strtolower($player->getName())) {
                    ++$count;
                }
        }
        $num = ($count + 1);
        $clipboard = "UNDO-" . strtolower($player->getName()) . "-" . $num;
        $this->getBlockArea((int)$x1, (int)$y1, (int)$z1, (int)$x2, (int)$y2, (int)$z2, 0, 0, 0, 0, $level, $player, $clipboard);
    }

    public function getBlockArea(int $x1, int $y1, int $z1, int $x2,int  $y2, int $z2, int $px, int $py, int $pz, $direction, World $level, Player $player, $clipboard)
    {

        $pos1 = [];
        $pos2 = [];

        if ($x1 > $x2) {
            $pos1[0] = $x2;
            $pos2[0] = $x1;
        } else if ($x1 < $x2) {
            $pos1[0] = $x1;
            $pos2[0] = $x2;
        } else {
            $pos1[0] = $x1;
            $pos2[0] = $x1;
        }

        if ($y1 > $y2) {
            $pos1[1] = $y2;
            $pos2[1] = $y1;
        } else if ($y1 < $y2) {
            $pos1[1] = $y1;
            $pos2[1] = $y2;
        } else {
            $pos1[1] = $y1;
            $pos2[1] = $y1;
        }

        if ($z1 > $z2) {
            $pos1[2] = $z2;
            $pos2[2] = $z1;
        } else if ($z1 < $z2) {
            $pos1[2] = $z1;
            $pos2[2] = $z2;
        } else {
            $pos1[2] = $z1;
            $pos2[2] = $z1;
        }

        $count = 0;

        $max = $this->calculateArea($pos1[0], $pos1[1], $pos1[2], $pos2[0], $pos2[1], $pos2[2]);
        $microt = microtime(true);

        $this->clipboard[$clipboard]['x'] = (abs($pos1[0] - $pos2[0]) + 1);
        $this->clipboard[$clipboard]['y'] = (abs($pos1[1] - $pos2[1]) + 1);
        $this->clipboard[$clipboard]['z'] = (abs($pos1[2] - $pos2[2]) + 1);
        $this->clipboard[$clipboard]['playerxpos'] = ($pos1[0] - $px);
        $this->clipboard[$clipboard]['playerypos'] = ($pos1[1] - $py);
        $this->clipboard[$clipboard]['playerzpos'] = ($pos1[2] - $pz);
        $this->clipboard[$clipboard]['direction'] = $direction;
        $this->clipboard[$clipboard]['date'] = date("YmdHis");
        $this->clipboard[$clipboard]['level'] = $level->getFolderName();
        $this->clipboard[$clipboard]['block'] = [];
        $this->clipboard[$clipboard]['sign'] = [];

        for ($x = $pos1[0]; $x <= $pos2[0]; $x++)
            for ($y = $pos1[1]; $y <= $pos2[1]; $y++)
                for ($z = $pos1[2]; $z <= $pos2[2]; $z++) {
                    ++$count;
                    if ((microtime(true) - $microt) > 0.1 || $count == 0 || $max == $count) {
                        $player->sendPopup($this->makeProgressBar($count, $max, '§a', '§a', '§0'));
                        $microt = microtime(true);
                    }
                    $targetid = $level->getBlockAt($x, $y, $z)->getId();
                    if ($targetid == "63" || $targetid == "68") {
                        $tile = $level->getTile(new Vector3 ($x, $y, $z));
                        if ($tile instanceof Sign) {
                            $signdata = "";
                            for ($i = 0; $i < 4; $i++)
                                $signdata .= $tile->getText()[$i] . "#@n@#";
                            $this->clipboard[$clipboard]['sign'][] = $signdata;
                        }
                    }

                    $this->clipboard[$clipboard]['block'][] = $targetid . ':' . $level->getBlockAt($x, $y, $z)->getMeta();
                }
    }

    public function setBlockAreaByClipboard($x1, $y1, $z1, $direction, World $level, Player $player, $clipboard)
    {

        $xlength = $this->clipboard[$clipboard]['x'];
        $ylength = $this->clipboard[$clipboard]['y'];
        $zlength = $this->clipboard[$clipboard]['z'];

        switch ($this->getRotation($direction, $this->clipboard[$clipboard]['direction'])) {
            case 0:
                $x1 = ($x1 + $this->clipboard[$clipboard]['playerxpos']);
                $y1 = ($y1 + $this->clipboard[$clipboard]['playerypos']);
                $z1 = ($z1 + $this->clipboard[$clipboard]['playerzpos']);
                $startX = $x1;
                $endX = ($x1 + $xlength - 1);
                $startY = $y1;
                $endY = ($y1 + $ylength - 1);
                $startZ = $z1;
                $endZ = ($z1 + $zlength - 1);
                break;
            case 1:
                $x1 = ($x1 - $this->clipboard[$clipboard]['playerzpos']);
                $y1 = ($y1 + $this->clipboard[$clipboard]['playerypos']);
                $z1 = ($z1 + $this->clipboard[$clipboard]['playerxpos']);
                $startX = $x1;
                $endX = ($x1 - $zlength + 1);
                $startY = $y1;
                $endY = ($y1 + $ylength - 1);
                $startZ = $z1;
                $endZ = ($z1 + $xlength - 1);
                break;
            case 2:
                $x1 = ($x1 - $this->clipboard[$clipboard]['playerxpos']);
                $y1 = ($y1 + $this->clipboard[$clipboard]['playerypos']);
                $z1 = ($z1 - $this->clipboard[$clipboard]['playerzpos']);
                $startX = $x1;
                $endX = ($x1 - $xlength + 1);
                $startY = $y1;
                $endY = ($y1 + $ylength - 1);
                $startZ = $z1;
                $endZ = ($z1 - $zlength + 1);
                break;
            case 3:
                $x1 = ($x1 + $this->clipboard[$clipboard]['playerzpos']);
                $y1 = ($y1 + $this->clipboard[$clipboard]['playerypos']);
                $z1 = ($z1 - $this->clipboard[$clipboard]['playerxpos']);
                $startX = $x1;
                $endX = ($x1 + $zlength - 1);
                $startY = $y1;
                $endY = ($y1 + $ylength - 1);
                $startZ = $z1;
                $endZ = ($z1 - $xlength + 1);
                break;
            default:
                return;
        }

        $count = 0;
        $microt = microtime(true);
        $max = $this->calculateArea($startX, $startY, $startZ, $endX, $endY, $endZ);

        $temparray = $this->clipboard[$clipboard]['block'];

        switch ($this->getRotation($direction, $this->clipboard[$clipboard]['direction'])) {
            case 0:
                for ($x = $startX; $x <= $endX; $x++)
                    for ($y = $startY; $y <= $endY; $y++)
                        for ($z = $startZ; $z <= $endZ; $z++) {
                            ++$count;
                            if ((microtime(true) - $microt) > 0.2 || $count == 0 || $max == $count) {
                                $player->sendPopup($this->makeProgressBar($count, $max, '§a', '§a', '§0'));
                                $microt = microtime(true);
                            }
                            $b = explode(':', array_shift($temparray));
                            $level->setBlock(new Vector3((int)$x, (int)$y, (int)$z), BlockFactory::getInstance()->get((int)$b[0], (int)$b[1]), false);
                        }
                return;
            ////////////////////////////
            case 1:
                for ($z = $startZ; $z <= $endZ; $z++)
                    for ($y = $startY; $y <= $endY; $y++)
                        for ($x = $startX; $x >= $endX; $x--) {
                            ++$count;
                            if ((microtime(true) - $microt) > 0.2 || $count == 0 || $max == $count) {
                                $player->sendPopup($this->makeProgressBar($count, $max, '§a', '§a', '§0'));
                                $microt = microtime(true);
                            }
                            $b = explode(':', array_shift($temparray));
                            if ($this->isAfter($b[0])) {
                                if (isset($this->stair[$b[0]])) {
                                    $b[1] = $this->getStairRotation($b[1], 1);
                                } else if (isset($this->wood[$b[0]])) {
                                    $b[1] = $this->getWoodRotation($b[1], 1);
                                } else if (isset($this->wall[$b[0]])) {
                                    $b[1] = $this->getWallRotation($b[1], 1);
                                } else if (isset($this->door[$b[0]])) {
                                    $b[1] = $this->getDoorRotation($b[1], 1);
                                } else if (isset($this->sign[$b[0]])) {
                                    $b[1] = $this->getSignRotation($b[1], 1);
                                } else if (isset($this->torch[$b[0]])) {
                                    $b[1] = $this->getTorchRotation($b[1], 1);
                                } else if (isset($this->bed[$b[0]])) {
                                    $b[1] = $this->getBedRotation($b[1], 1);
                                }
                            }

                            $level->setBlock(new Vector3((int)$x, (int)$y, (int)$z), BlockFactory::getInstance()->get((int)$b[0], (int)$b[1]), false);
                        }
                return;
            ////////////////////////////
            case 2:
                for ($x = $startX; $x >= $endX; $x--)
                    for ($y = $startY; $y <= $endY; $y++)
                        for ($z = $startZ; $z >= $endZ; $z--) {
                            ++$count;
                            if ((microtime(true) - $microt) > 0.2 || $count == 0 || $max == $count) {
                                $player->sendPopup($this->makeProgressBar($count, $max, '§a', '§a', '§0'));
                                $microt = microtime(true);
                            }
                            $b = explode(':', array_shift($temparray));
                            if ($this->isAfter($b[0])) {
                                if (isset($this->stair[$b[0]])) {
                                    $b[1] = $this->getStairRotation($b[1], 2);
                                } else if (isset($this->wood[$b[0]])) {
                                    $b[1] = $this->getWoodRotation($b[1], 2);
                                } else if (isset($this->wall[$b[0]])) {
                                    $b[1] = $this->getWallRotation($b[1], 2);
                                } else if (isset($this->door[$b[0]])) {
                                    $b[1] = $this->getDoorRotation($b[1], 2);
                                } else if (isset($this->sign[$b[0]])) {
                                    $b[1] = $this->getSignRotation($b[1], 2);
                                } else if (isset($this->torch[$b[0]])) {
                                    $b[1] = $this->getTorchRotation($b[1], 2);
                                } else if (isset($this->bed[$b[0]])) {
                                    $b[1] = $this->getBedRotation($b[1], 2);
                                }
                            }
                            $level->setBlock(new Vector3((int)$x, (int)$y, (int)$z), BlockFactory::getInstance()->get((int)$b[0], (int)$b[1]), false);
                        }
                return;
            ////////////////////////////
            case 3:
                for ($z = $startZ; $z >= $endZ; $z--)
                    for ($y = $startY; $y <= $endY; $y++)
                        for ($x = $startX; $x <= $endX; $x++) {
                            ++$count;
                            if ((microtime(true) - $microt) > 0.2 || $count == 0 || $max == $count) {
                                $player->sendPopup($this->makeProgressBar($count, $max, '§a', '§a', '§0'));
                                $microt = microtime(true);
                            }
                            $b = explode(':', array_shift($temparray));
                            if ($this->isAfter($b[0])) {
                                if (isset($this->stair[$b[0]])) {
                                    $b[1] = $this->getStairRotation($b[1], 3);
                                } else if (isset($this->wood[$b[0]])) {
                                    $b[1] = $this->getWoodRotation($b[1], 3);
                                } else if (isset($this->wall[$b[0]])) {
                                    $b[1] = $this->getWallRotation($b[1], 3);
                                } else if (isset($this->door[$b[0]])) {
                                    $b[1] = $this->getDoorRotation($b[1], 3);
                                } else if (isset($this->sign[$b[0]])) {
                                    $b[1] = $this->getSignRotation($b[1], 3);
                                } else if (isset($this->torch[$b[0]])) {
                                    $b[1] = $this->getTorchRotation($b[1], 3);
                                } else if (isset($this->bed[$b[0]])) {
                                    $b[1] = $this->getBedRotation($b[1], 3);
                                }
                            }
                            $level->setBlock(new Vector3((int)$x, (int)$y, (int)$z), BlockFactory::getInstance()->get((int)$b[0], (int)$b[1]), false);
                        }
                return;
            ////////////////////////////
            default:
                break;
        }
    }

    public function executeUndo(Player $player)
    {
        $count = 0;
        foreach (array_keys($this->clipboard) as $key) {
            $info = explode('-', $key);
            if (count($info) == 3)
                if ($info[1] == strtolower($player->getName()))
                    ++$count;
        }
        $clipboard = "UNDO-" . strtolower($player->getName()) . "-" . $count;
        if (isset($this->clipboard[$clipboard])) {
            $level = $this->getServer()->getWorldManager()->getWorldByName($this->clipboard[$clipboard]['level']);
            $this->setBlockAreaByClipboard(0, 0, 0, 0, $level, $player, $clipboard);
            unset($this->clipboard[$clipboard]);
        }
    }
	
}