<?php
declare(strict_types=1);

namespace Solo\WorldEdit\trait;

trait RotationTrait
{
	public function getStairRotation($dmg, $rotation): int
	{
		if ($dmg > 3) {
			$ret = 4;
			$dmg -= 4;
		} else $ret = 0;
		if ($rotation == 0) return $dmg;
		if ($rotation == 1) {
			if ($dmg == 0) return $ret + 2;
			if ($dmg == 2) return $ret + 1;
			if ($dmg == 1) return $ret + 3;
			if ($dmg == 3) return $ret;
		}
		if ($rotation == 2) {
			if ($dmg == 0) return $ret + 1;
			if ($dmg == 2) return $ret + 3;
			if ($dmg == 1) return $ret;
			if ($dmg == 3) return $ret + 2;
		}
		if ($rotation == 3) {
			if ($dmg == 0) return $ret + 3;
			if ($dmg == 2) return $ret;
			if ($dmg == 1) return $ret + 2;
			if ($dmg == 3) return $ret + 1;
		}
		return (int)$dmg;
	}
	
	public function getWoodRotation($dmg, $rotation): int
	{
		if ($dmg < 4) return $dmg;
		if ($rotation == 0 or $rotation == 2) return $dmg;
		if ($rotation == 1 or $rotation == 3) return $dmg + 4;
		return (int)$dmg;
	}
	
	public function getDoorRotation($dmg, $rotation):int
	{
		if ($dmg < 4) {
			if (($dmg + $rotation) > 3) return ($dmg + $rotation - 4);
			else return ($dmg + $rotation);
		}
		if ($dmg < 8) {
			if (($dmg + $rotation) > 7) return ($dmg + $rotation - 4);
			else return ($dmg + $rotation);
		}
		return (int)$dmg;
	}
	
	public function getWallRotation($dmg, $rotation):int
	{
		if($rotation == 0) return $dmg;
		if($rotation == 1)
		{
			if($dmg == 5) return 3;
			if($dmg == 3) return 4;
			if($dmg == 4) return 2;
			if($dmg == 2) return 5;
		}
		if($rotation == 2)
		{
			if($dmg == 5) return 4;
			if($dmg == 3) return 2;
			if($dmg == 4) return 5;
			if($dmg == 2) return 3;
		}
		if($rotation == 3)
		{
			if($dmg == 5) return 2;
			if($dmg == 3) return 5;
			if($dmg == 4) return 3;
			if($dmg == 2) return 4;
		}
		return (int)$dmg;
	}
	
	public function getBedRotation($dmg, $rotation):int
	{
		if ($dmg < 4) {
			if (($dmg + $rotation) > 3) return ($dmg + $rotation - 4);
			else return ($dmg + $rotation);
		} else if ($dmg < 8) {
			if (($dmg + $rotation) > 7) return ($dmg + $rotation - 4);
			else return ($dmg + $rotation);
		} else if ($dmg < 12) {
			if (($dmg + $rotation) > 11) return ($dmg + $rotation - 4);
			else return ($dmg + $rotation);
		} else {
			return (int)$dmg;
		}
	}
	
	public function getSignRotation($dmg, $rotation): float|int
	{
		if ($dmg + ($rotation * 4) > 15) return ($dmg + ($rotation * 4) - 16);
		else return $dmg + ($rotation * 4);
	}
	
	public function getTorchRotation($dmg, $rotation):int
	{
		if ($dmg == 0) return $dmg;
		if($rotation == 0) return $dmg;
		if($rotation == 1){
			if($dmg == 1) return 3;
			if($dmg == 3) return 2;
			if($dmg == 2) return 4;
			if($dmg == 4) return 1;
		}
		if($rotation == 2){
			if($dmg == 1) return 2;
			if($dmg == 3) return 4;
			if($dmg == 2) return 1;
			if($dmg == 4) return 3;
		}
		if($rotation == 3){
			if($dmg == 1) return 4;
			if($dmg == 3) return 1;
			if($dmg == 2) return 3;
			if($dmg == 4) return 2;
		}
		return (int)$dmg;
	}
	
	
	public function getRotation($pd, $cd): bool|int
	{
		if($cd == 0)
		{
			if($pd == 0) return 0;
			else if($pd == 1) return 1;
			else if($pd == 2) return 2;
			else if($pd == 3) return 3;
			else return false;
		}
		if($cd == 1)
		{
			if($pd == 0) return 3;
			else if($pd == 1) return 0;
			else if($pd == 2) return 1;
			else if($pd == 3) return 2;
			else return false;
		}
		if($cd == 2)
		{
			if($pd == 0) return 2;
			else if($pd == 1) return 3;
			else if($pd == 2) return 0;
			else if($pd == 3) return 1;
			else return false;
		}
		if($cd == 3)
		{
			if($pd == 0) return 1;
			else if($pd == 1) return 2;
			else if($pd == 2) return 3;
			else if($pd == 3) return 0;
			else return false;
		}
		return false;
	}
}