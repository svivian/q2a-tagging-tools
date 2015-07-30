<?php
/*
	Question2Answer Tagging Tools plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

require_once QA_INCLUDE_DIR.'qa-app-posts.php';
require_once 'qa-tt-helper.php';

class qa_tagging_tools
{
	private $directory;
	private $urltoroot;

	public function filter_question(&$question, &$errors, $oldquestion)
	{
		// replace tag synonyms
		$config = trim(qa_opt('tagging_tools_synonyms'));
		if (!empty($config)) {
			$synonyms = qa_tt_helper::synonyms_to_array($config);
			$question['tags'] = qa_tt_helper::convert_tags($question['tags'], $synonyms);

			// copied from qa_filter_basic
			$mintags = qa_opt('min_num_q_tags');
			if (count($question['tags']) < $mintags) {
				$errors['tags'] = qa_lang_sub('question/min_tags_x', $mintags);
				return;
			}
		}

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
			$msg = strtr(qa_lang_html('taggingtools/tags_not_usable'), array(
				'^1' => qa_html($reqPoints),
				'^2' => qa_html(implode(', ', $errorTags)),
			));
			$errors['tags'] = $msg;
		}
	}

	public function load_module($directory, $urltoroot)
	{
		$this->directory = $directory;
		$this->urltoroot = $urltoroot;
	}

	public function option_default($option)
	{
		switch ($option) {
			case 'tagging_tools_synonyms':
				return '';
			case 'tagging_tools_prevent':
				return 0;
			case 'tagging_tools_rep':
				return 100;
		}
	}

	public function admin_form(&$qa_content)
	{
		// process config change
		$saved_msg = '';
		$js = '';

		if (qa_clicked('tagging_tools_save_button')) {
			qa_opt('tagging_tools_synonyms', strtolower(trim(qa_post_text('tagging_tools_synonyms'))));
			qa_opt('tagging_tools_prevent', (int) qa_post_text('tagging_tools_prevent'));
			qa_opt('tagging_tools_rep', (int) qa_post_text('tagging_tools_rep'));
			$saved_msg = '<div id="tagging_tools_recalc">'.qa_lang_html('taggingtools/admin_saved').'</div>';

			// convert all old tags based on synonyms
			if (qa_post_text('tagging_tools_convert')) {
				$saved_msg = '<div id="tagging_tools_recalc">'.qa_lang_html('taggingtools/recalc_start').'</div>';
				$js = file_get_contents($this->directory.'/tag-admin.js');

				$replace = array(
					'VAR_LANG_PROGESS' => qa_js(qa_lang('taggingtools/recalc_progress')),
					'VAR_LANG_ERROR' => qa_js(qa_lang('taggingtools/recalc_error')),
					'VAR_LANG_DONE' => qa_js(qa_lang('taggingtools/recalc_done')),
				);
				$js = strtr($js, $replace);
			}
		}

		// set fields to show/hide when checkbox is clicked
		qa_set_display_rules($qa_content, array(
			'tagging_tools_rep' => 'tagging_tools_prevent',
		));

		return array(
			'ok' => $saved_msg,

			'fields' => array(
				array(
					'label' => qa_lang_html('taggingtools/admin_synonyms'),
					'tags' => 'name="tagging_tools_synonyms" id="tagging_tools_synonyms"',
					'value' => qa_opt('tagging_tools_synonyms'),
					'type' => 'textarea',
					'rows' => 20,
					'note' => qa_lang_html('taggingtools/admin_synonyms_note'),
				),
				array(
					'label' => qa_lang_html('taggingtools/admin_convert'),
					'tags' => 'name="tagging_tools_convert" id="tagging_tools_convert"',
					'value' => '',
					'type' => 'checkbox',
				),

				array(
					'label' => qa_lang_html('taggingtools/admin_prevent'),
					'tags' => 'name="tagging_tools_prevent" id="tagging_tools_prevent"',
					'value' => qa_opt('tagging_tools_prevent'),
					'type' => 'checkbox',
				),

				array(
					'label' => qa_lang_html('taggingtools/admin_minpoints'),
					'id' => 'tagging_tools_rep',
					'value' => qa_opt('tagging_tools_rep'),
					'tags' => 'name="tagging_tools_rep"',
					'type' => 'number',
				),

				array(
					'type' => 'custom',
					'html' => '<script>'.$js.'</script>',
				),
			),

			'buttons' => array(
				array(
					'label' => qa_lang_html('main/save_button'),
					'tags' => 'name="tagging_tools_save_button"',
				),
			),
		);
	}
}
