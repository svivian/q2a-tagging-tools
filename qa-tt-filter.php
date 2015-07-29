<?php
/*
	Question2Answer Tagging Tools plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

class qa_tagging_tools_filter
{
	public function filter_question(&$question, &$errors, $oldquestion)
	{
		$reqPoints = qa_opt('tagging_tools_rep');
		$userPoints = qa_get_logged_in_points();

		// quit early if user has enough rep
		if ($userPoints > $reqPoints)
			return;

		// escape data
		$tags = array();
		foreach ($question['tags'] as $tag)
			$tags[] = "'" . qa_db_escape_string($tag) . "'";

		// get tag counts from database
		$sql = 'SELECT word, tagcount FROM ^words WHERE word IN (' . implode(',', $tags) . ')';
		$result = qa_db_query_sub($sql);

		$existingTags = array();
		foreach (qa_db_read_all_assoc($result) as $row) {
			$existingTags[$row['word']] = $row['tagcount'];
		}

		// check if submitted tags are allowed
		$errorTags = array();
		foreach ($question['tags'] as $tag) {
			if (!isset($existingTags[$tag]) || $existingTags[$tag] == 0)
				$errorTags[] = $tag;
		}

		if (count($errorTags)) {
			$msg = 'You need '.$reqPoints.' points to create new tags. The following tags are not allowed: '.qa_html(implode(', ', $errorTags));
			$errors['tags'] = $msg;
		}

	}
}
