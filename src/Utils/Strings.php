<?php namespace MeowCrew\MembersBasedPricing\Utils;

class Strings {

	public static function startsWith( $haystack, $needle ) {
		$length = strlen( $needle );

		return substr( $haystack, 0, $length ) === $needle;
	}

	public static function endsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		if ( ! $length ) {
			return true;
		}

		return substr( $haystack, - $length ) === $needle;
	}
}
