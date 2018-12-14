<?php
/*
	Question2Answer Tagging Tools plugin
	Copyright (C) 2011 Scott Vivian
	License: https://www.gnu.org/licenses/gpl.html
*/

require_once QA_INCLUDE_DIR . 'app/posts.php';
require_once 'qa-tt-helper.php';

class qa_tagging_tools
{
	private $directory;
	private $urltoroot;

	public function filter_question(&$question, &$errors, $oldquestion)
	{
		// quit early if there are no tags for some reason
		if (!isset($question['tags']))
			return;

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

		// check for tags too long/short
		$this->tagLengthCheck($question, $errors);
		if (!empty($errors))
			return;

		// check if user can use these tags
		$this->newTagCheck($question, $errors);
	}

	private function tagLengthCheck(&$question, &$errors)
	{
		$tagMinLength = (int) qa_opt('tagging_tools_min_length');
		$tagMaxLength = (int) qa_opt('tagging_tools_max_length');

		$badLengthTags = [];
		foreach ($question['tags'] as $tag) {
			$tagLength = qa_strlen($tag);
			if ($tagLength < $tagMinLength || ($tagMaxLength > 0 && $tagLength > $tagMaxLength)) {
				$badLengthTags[] = $tag;
			}
		}

		if (!empty($badLengthTags)) {
			$errors['tags'] = strtr(qa_lang_html('taggingtools/tag_bad_length'), [
				'^1' => $tagMinLength,
				'^2' => $tagMaxLength,
				'^3' => qa_html(implode(', ', $badLengthTags)),
			]);
		}
	}

	private function newTagCheck(&$question, &$errors)
	{
		$tagPrevent = qa_opt('tagging_tools_prevent');
		$reqPoints = qa_opt('tagging_tools_rep');
		$userPoints = qa_get_logged_in_points();

		// quit early if user has enough rep, or no tags to process
		if (!$tagPrevent || $userPoints >= $reqPoints || empty($question['tags']))
			return;

		// escape data
		$tags = [];
		foreach ($question['tags'] as $tag)
			$tags[] = "'" . qa_db_escape_string($tag) . "'";
		$sqlTagString = implode(',', $tags);

		// get tag counts from database
		$sql = "SELECT word FROM ^words WHERE word IN ($sqlTagString) AND tagcount > 0";
		$result = qa_db_query_sub($sql);
		$existingTags = qa_db_read_all_values($result);

		// check if submitted tags are allowed
		$errorTags = [];
		foreach ($question['tags'] as $tag) {
			if (!in_array($tag, $existingTags))
				$errorTags[] = $tag;
		}

		if (count($errorTags)) {
			$errors['tags'] = strtr(qa_lang_html('taggingtools/tags_not_usable'), [
				'^1' => qa_html($reqPoints),
				'^2' => qa_html(implode(', ', $errorTags)),
			]);
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
			case 'tagging_tools_min_length':
				return 0;
			case 'tagging_tools_max_length':
				return 0;
			case 'tagging_tools_prevent':
				return 0;
			case 'tagging_tools_rep':
				return 100;
			case 'tagging_tools_redirect':
				return 0;
		}
	}

	public function admin_form(&$qa_content)
	{
		// process config change
		$savedMsg = '';
		$js = '';

		if (qa_clicked('tagging_tools_save_button')) {
			qa_opt('tagging_tools_synonyms', strtolower(trim(qa_post_text('tagging_tools_synonyms'))));
			qa_opt('tagging_tools_min_length', (int) qa_post_text('tagging_tools_min_length'));
			qa_opt('tagging_tools_max_length', (int) qa_post_text('tagging_tools_max_length'));
			qa_opt('tagging_tools_prevent', (int) qa_post_text('tagging_tools_prevent'));
			qa_opt('tagging_tools_rep', (int) qa_post_text('tagging_tools_rep'));
			qa_opt('tagging_tools_redirect', (int) qa_post_text('tagging_tools_redirect'));

			$savedMsg = '<div id="tagging_tools_recalc">' . qa_lang_html('admin/options_saved') . '</div>';

			// convert all old tags based on synonyms
			if (qa_post_text('tagging_tools_convert')) {
				$savedMsg = '<div id="tagging_tools_recalc">' . qa_lang_html('taggingtools/recalc_start') . '</div>';
				$js = file_get_contents($this->directory . '/tag-admin.js');

				$replace = [
					'VAR_LANG_PROGESS' => qa_js(qa_lang('taggingtools/recalc_progress')),
					'VAR_LANG_ERROR' => qa_js(qa_lang('taggingtools/recalc_error')),
					'VAR_LANG_DONE' => qa_js(qa_lang('taggingtools/recalc_done')),
				];
				$js = strtr($js, $replace);
			}
		}

		// set fields to show/hide when checkbox is clicked
		qa_set_display_rules($qa_content, [
			'tagging_tools_rep' => 'tagging_tools_prevent',
		]);

		return [
			'ok' => $savedMsg,
			'style' => 'wide',

			'fields' => [
				[
					'style' => 'tall',
					'label' => qa_lang_html('taggingtools/admin_synonyms'),
					'tags' => 'name="tagging_tools_synonyms" id="tagging_tools_synonyms"',
					'value' => qa_opt('tagging_tools_synonyms'),
					'type' => 'textarea',
					'rows' => 12,
					'note' => preg_replace('/`(.+?)`/', '<code>$1</code>', qa_lang_html('taggingtools/admin_synonyms_note')),
				],
				[
					'style' => 'tall',
					'label' => qa_lang_html('taggingtools/admin_convert'),
					'tags' => 'name="tagging_tools_convert" id="tagging_tools_convert"',
					'value' => '',
					'type' => 'checkbox',
				],
				['type' => 'blank'],
				[
					'label' => qa_lang_html('taggingtools/admin_min_tag_length'),
					'id' => 'tagging_tools_min_length',
					'value' => qa_opt('tagging_tools_min_length'),
					'tags' => 'name="tagging_tools_min_length"',
					'type' => 'number',
				],
				[
					'label' => qa_lang_html('taggingtools/admin_max_tag_length'),
					'id' => 'tagging_tools_max_length',
					'value' => qa_opt('tagging_tools_max_length'),
					'tags' => 'name="tagging_tools_max_length"',
					'type' => 'number',
					'note' => qa_lang_html('taggingtools/admin_max_tag_length_note'),
				],
				[
					'label' => qa_lang_html('taggingtools/admin_prevent'),
					'tags' => 'name="tagging_tools_prevent" id="tagging_tools_prevent"',
					'value' => qa_opt('tagging_tools_prevent'),
					'type' => 'checkbox',
				],
				[
					'label' => qa_lang_html('taggingtools/admin_min_points'),
					'id' => 'tagging_tools_rep',
					'value' => qa_opt('tagging_tools_rep'),
					'tags' => 'name="tagging_tools_rep"',
					'type' => 'number',
				],
				[
					'label' => qa_lang_html('taggingtools/admin_redirect'),
					'tags' => 'name="tagging_tools_redirect" id="tagging_tools_redirect"',
					'value' => qa_opt('tagging_tools_redirect'),
					'type' => 'checkbox',
				],
				[
					'style' => 'tall',
					'type' => 'custom',
					'html' => '<script>' . $js . '</script>',
				],
			],

			'buttons' => [
				[
					'label' => qa_lang_html('main/save_button'),
					'tags' => 'name="tagging_tools_save_button"',
				],
			],
		];
	}
}
