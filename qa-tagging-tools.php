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
			$msg = 'You need '.$reqPoints.' points to create new tags. The following tags are not allowed: '.qa_html(implode(', ', $errorTags));
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
			$saved_msg = '<div id="tagging_tools_recalc">Tag Synonyms settings saved</div>';

			// convert all old tags based on synonyms
			if (qa_post_text('tagging_tools_convert')) {
				$saved_msg = '<div id="tagging_tools_recalc">Editing tags...</div>';
				$js = file_get_contents($this->directory.'/tag-admin.js');
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
					'label' => 'Tag Synonyms',
					'tags' => 'name="tagging_tools_synonyms" id="tagging_tools_synonyms"',
					'value' => qa_opt('tagging_tools_synonyms'),
					'type' => 'textarea',
					'rows' => 20,
					'note' => 'Put each pair of synonyms on a new line. <code>q2a,question2answer</code> means that a tag of <code>q2a</code> will be replaced by <code>question2answer</code>, while <code>help</code> on its own means that tag will be removed.',
				),
				array(
					'label' => 'Also convert existing tags using above rules',
					'tags' => 'name="tagging_tools_convert" id="tagging_tools_convert"',
					'value' => '',
					'type' => 'checkbox',
				),

				array(
					'label' => 'Prevent new users from creating new tags',
					'tags' => 'name="tagging_tools_prevent" id="tagging_tools_prevent"',
					'value' => qa_opt('tagging_tools_prevent'),
					'type' => 'checkbox',
				),

				array(
					'id' => 'tagging_tools_rep',
					'label' => 'Minimum reputation to create new tags',
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
					'label' => 'Save Changes',
					'tags' => 'name="tagging_tools_save_button"',
				),
			),
		);
	}
}
