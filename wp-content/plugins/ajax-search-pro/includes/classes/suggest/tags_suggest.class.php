<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_tagsKeywordSuggest')) {
    class wpd_tagsKeywordSuggest extends wpd_keywordSuggestAbstract {

        function __construct($args = array()) {
	        $defaults = array(
		        'maxCount' => 10,
		        'maxCharsPerWord' => 25,
		        'match_start' => false
	        );
	        $args = wp_parse_args( $args, $defaults );

            $this->maxCount = $args['maxCount'];
            $this->maxCharsPerWord = $args['maxCharsPerWord'];
	        $this->matchStart = $args['match_start'];
        }

        function getKeywords($q) {
            $res = array();

            if (strlen($q) == 0) return;

            $tags = get_tags(array('search' => $q, 'number'=>$this->maxCount));

            foreach ($tags as $tag) {
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