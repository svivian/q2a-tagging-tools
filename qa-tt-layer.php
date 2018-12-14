<?php
/*
	Question2Answer Tagging Tools plugin
	Copyright (C) 2011 Scott Vivian
	License: https://www.gnu.org/licenses/gpl.html
*/

class qa_html_theme_layer extends qa_html_theme_base
{
	public function head_script()
	{
		qa_html_theme_base::head_script();

		$userPoints = qa_get_logged_in_points();
		$userLevel = qa_get_logged_in_level();

		// skip JS if no permission to post
		if (qa_user_permit_error('permit_post_q') !== false)
			return;

		if (!$this->forbidNewTags($userPoints, $userLevel))
			return;

		$jsVars =
			'var taggingTools = {' .
			'separator: ' . (qa_opt('tag_separator_comma') ? qa_js(',') : qa_js(' ')) .
			', points: ' . qa_js(qa_opt('tagging_tools_rep')) .
			', minLength: ' . qa_js(qa_opt('tagging_tools_min_length')) .
			', maxLength: ' . qa_js(qa_opt('tagging_tools_max_length')) .
			', newTagErrorTemplate: ' . qa_js(qa_lang('taggingtools/tag_not_usable_js')) .
			', lengthErrorTemplate: ' . qa_js(qa_lang('taggingtools/tag_bad_length_js')) .
			'};';

		$js = file_get_contents(QA_HTML_THEME_LAYER_DIRECTORY . '/tag-filter.js');
		$this->output_raw('<script>' . $jsVars . $js . '</script>');
	}

	private function forbidNewTags($userPoints, $userLevel)
	{
		$qEditForm = $this->template == 'ask' || isset($this->content['form_q_edit']);

		return
			$qEditForm &&
			qa_opt('tagging_tools_prevent') &&
			$userPoints < (int) qa_opt('tagging_tools_rep') &&
			$userLevel < QA_USER_LEVEL_EXPERT;
	}
}
