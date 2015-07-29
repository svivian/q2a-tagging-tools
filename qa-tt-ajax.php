<?php
/*
	Question2Answer Tagging Tools plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

require_once QA_INCLUDE_DIR.'qa-app-posts.php';
require_once QA_INCLUDE_DIR.'qa-util-string.php';
require_once 'qa-tt-helper.php';

class qa_tagging_tools_ajax
{
	private $process_tags = 5;

	public function match_request($request)
	{
		return $request == 'ajax-tagging-tools';
	}

	public function process_request($request)
	{
		$userlevel = qa_get_logged_in_level();
		if ($userlevel < QA_USER_LEVEL_SUPER)
			return;

		$synonyms = qa_tt_helper::synonyms_to_array(qa_opt('tagging_tools_synonyms'));
		$from = array();
		foreach ($synonyms as $syn)
			$from[] = "'" . qa_db_escape_string($syn['from']) . "'";

		// basic select
		$sql_suffix = 'FROM ^posts p, ^posttags t, ^words w WHERE w.wordid=t.wordid AND p.postid=t.postid AND BINARY w.word IN (' . implode(',', $from) . ')';

		// get total
		$sql_count = 'SELECT count(*) AS total '.$sql_suffix;
		$result = qa_db_query_sub($sql_count);
		$count = qa_db_read_one_assoc($result, true);

		if ($count['total'] == 0) {
			echo '0';
			return;
		}

		// get some posts to edit
		$sql = 'SELECT p.postid, BINARY p.tags AS tags '.$sql_suffix.' LIMIT '.$this->process_tags;
		$result = qa_db_query_sub($sql);
		$questions = qa_db_read_all_assoc($result);

		qa_suspend_event_reports(true); // avoid infinite loop
		$userid = qa_get_logged_in_userid();
		foreach ($questions as $q) {
			$oldtags = qa_tagstring_to_tags(@$q['tags']);
			$newtags = qa_tt_helper::convert_tags($oldtags, $synonyms);
			qa_post_set_content($q['postid'], null, null, null, $newtags, null, null, $userid);
		}
		qa_suspend_event_reports(false);

		echo $count['total'];
	}
}
