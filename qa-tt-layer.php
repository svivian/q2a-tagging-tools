<?php
/*
	Question2Answer Tagging Tools plugin
	License: http://www.gnu.org/licenses/gpl.html
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

		$replace = [
			'VAR_TAG_SEPARATOR' => qa_opt('tag_separator_comma') ? qa_js(',') : qa_js(' '),
			'VAR_TAG_POINTS' => number_format(qa_opt('tagging_tools_rep')),
			'VAR_TAG_ERROR' => qa_js(qa_lang('taggingtools/tag_not_usable_js')),
		];
		$js = file_get_contents(QA_HTML_THEME_LAYER_DIRECTORY . '/tag-filter.js');
		$js = strtr($js, $replace);

		$this->output_raw('<script>' . $js . '</script>');
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
