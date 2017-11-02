<?php
/*
	Question2Answer Tagging Tools plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

require_once QA_INCLUDE_DIR.'app/posts.php';
require_once QA_INCLUDE_DIR.'util/string.php';
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
		foreach ($synonyms as $syn) {
			$from[] = "'" . qa_db_escape_string($syn['from']) . "'";
		}

		if (empty($from)) {
			echo '0';
			return;
		}

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
			$this->qa_post_set_content($q['postid'], null, null, null, $newtags, null, null, $userid);
		}
		qa_suspend_event_reports(false);

		echo $count['total'];
	}

	// TEMPORARY duplicate of qa_post_set_content in order to allow saving silently
	private function qa_post_set_content($postid, $title, $content, $format = null, $tags = null, $notify = null, $email = null, $byuserid = null, $extravalue = null, $name = null)
	{
		$oldpost = qa_post_get_full($postid, 'QAC');

		if (!isset($title))
			$title = $oldpost['title'];

		if (!isset($content))
			$content = $oldpost['content'];

		if (!isset($format))
			$format = $oldpost['format'];

		if (!isset($tags))
			$tags = qa_tagstring_to_tags($oldpost['tags']);

		if (isset($notify) || isset($email))
			$setnotify = qa_combine_notify_email($oldpost['userid'], isset($notify) ? $notify : isset($oldpost['notify']),
				isset($email) ? $email : $oldpost['notify']);
		else
			$setnotify = $oldpost['notify'];

		$byhandle = qa_userid_to_handle($byuserid);

		$text = qa_post_content_to_text($content, $format);

		switch ($oldpost['basetype']) {
			case 'Q':
				$tagstring = qa_post_tags_to_tagstring($tags);
				// SV: added parameters to save silently
				qa_question_set_content($oldpost, $title, $content, $format, $text, $tagstring, $setnotify, $byuserid, $byhandle, null, $extravalue, $name, false, true);
				break;

			case 'A':
				$question = qa_post_get_full($oldpost['parentid'], 'Q');
				// SV: added parameters to save silently
				qa_answer_set_content($oldpost, $content, $format, $text, $setnotify, $byuserid, $byhandle, null, $question, $name, false, true);
				break;

			case 'C':
				$parent = qa_post_get_full($oldpost['parentid'], 'QA');
				$question = qa_post_parent_to_question($parent);
				// SV: added parameters to save silently
				qa_comment_set_content($oldpost, $content, $format, $text, $setnotify, $byuserid, $byhandle, null, $question, $parent, $name, false, true);
				break;
		}
	}
}
