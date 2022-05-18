<?php
declare(strict_types = 1);

namespace Solo\WorldEdit;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_encode;
use function json_decode;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class JsonData{
	
	public static function call(string $path , string $data = '{}') :array{
		return json_decode(file_exists($path) ? file_get_contents($path) : $data, true);
	}
	
	public static function save(string $path, array $data) :void{
		file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	}
	
}
