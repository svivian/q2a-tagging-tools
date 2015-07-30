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

		if ($this->forbid_new_tag()) {
			$replace = array(
				'VAR_TAG_SEPARATOR' => qa_opt('tag_separator_comma') ? qa_js(',') : qa_js(' '),
				'VAR_TAG_POINTS' => number_format(qa_opt('tagging_tools_rep')),
				'VAR_TAG_ERROR' => qa_js(qa_lang('taggingtools/tag_not_usable_js')),
			);
			$js = file_get_contents(QA_HTML_THEME_LAYER_DIRECTORY.'/tag-filter.js');
			$js = strtr($js, $replace);

			$this->output_raw('<script>'.$js.'</script>');
		}
	}

	private function forbid_new_tag()
	{
		$q_edit = $this->template == 'ask' || isset($this->content['form_q_edit']);
		$tag_prevent = qa_opt('tagging_tools_prevent');

		if ($q_edit && $tag_prevent) {
			return
				qa_get_logged_in_points() < (int) qa_opt('tagging_tools_rep') &&
				qa_get_logged_in_level() < QA_USER_LEVEL_EXPERT;
		}

		return false;
	}
}
