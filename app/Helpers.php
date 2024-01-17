<?php
declare(strict_types=1);

namespace App;

class Helpers 
{
    public function MB_CASE_TITLE_BR(string $str) : string 
	{
		if(mb_internal_encoding() !== 'UTF-8') {
			$str = mb_convert_encoding($str, mb_internal_encoding(), 'UTF-8');
		}

		$mb_ucfirst = function($s) {
			return mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1, null);
		};

		$str     = mb_strtolower(trim(preg_replace('/\s+/', ' ', $str)));
		$strings = explode(' ', $str);
		$array[] = $mb_ucfirst($strings[0]);
		unset($strings[0]);

		foreach ($strings as $string) {
			if (!preg_match("/^([dn]?[aeiou]?[s]?|em)$/i", $string)) {
				$string = $mb_ucfirst($string);
			}
			$array[] = $string;
		}

		return implode(' ', $array);
    }
}