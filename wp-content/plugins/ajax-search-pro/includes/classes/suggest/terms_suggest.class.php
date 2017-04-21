<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_termsKeywordSuggest')) {
	class wpd_termsKeywordSuggest extends wpd_keywordSuggestAbstract {

		function __construct($args = array()) {
			$defaults = array(
				'maxCount' => 10,
				'maxCharsPerWord' => 25,
				'taxonomy' => 'post_tag',
				'match_start' => false
			);
			$args = wp_parse_args( $args, $defaults );

			$this->maxCount = $args['maxCount'];
			$this->maxCharsPerWord = $args['maxCharsPerWord'];
			$this->taxonomy = $args['taxonomy'];
			$this->matchStart = $args['match_start'];
		}

		function getKeywords($q) {
			$res = array();
			$tags = get_terms(array($this->taxonomy), array('search' => $q, 'hide_empty' => false));
			foreach ($tags as $tag) {
                if ( !is_object($tag) ) continue;
				$t = ASP_mb::strtolower($tag->name);
                $q = ASP_mb::strtolower($q);
				if (
					$t != $q &&
					('' != $str = wd_substr_at_word($t, $this->maxCharsPerWord))
				) {
					if ($this->matchStart && ASP_mb::strpos($t, $q) === 0)
						$res[] = $str;
					elseif (!$this->matchStart)
						$res[] = $str;
				}
			}
			return $res;
		}

	}
}