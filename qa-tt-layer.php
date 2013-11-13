<?php
/*
	Question2Answer Tagging Tools plugin, v1.5
	License: http://www.gnu.org/licenses/gpl.html
*/

class qa_html_theme_layer extends qa_html_theme_base
{
	// theme replacement functions

	function head_script()
	{
		qa_html_theme_base::head_script();

		if ( qa_opt( 'tagging_tools_prevent') )
		{
			$js =
				"<script>\n" .
				"function qa_tag_verify()\n" .
				"{\n" .
				"	var tags = jQuery('#tags').val().split(' ');\n" .
				"	var alltags = ','+qa_tags_complete+',';\n" .
				"	if ( jQuery('#tags').siblings('.qa-tag-synonyms-error').length > 0 )\n" .
				"		return false;\n\n" .

				"	for ( var i in tags )\n" .
				"	{\n" .
				"		if ( tags[i].length > 0 && alltags.indexOf(','+tags[i]+',') == -1 )\n" .
				"		{\n" .
				"			var error = '<div style=\"display:none\" class=\"qa-form-tall-error qa-tag-synonyms-error\">The tag \"'+tags[i]+'\" does not exist; you need " . number_format( qa_opt('tagging_tools_rep') ) . " points to create new tags.</div>';\n" .
				"			jQuery(error).insertAfter('#tags').slideDown('fast').delay(5000).slideUp('fast', function() { jQuery(this).detach() } );\n" .
				"			return false;\n" .
				"		}\n" .
				"	}\n\n" .

				"	document.ask.submit();\n" .
				"}\n" .

				"$(function(){\n" .
				"	$('.qa-form-tall-button-ask').on('click', qa_tag_verify);\n" .
				"});\n" .
				"</script>";

			$this->output_raw($js);
		}
	}

	// worker functions

	function _forbid_new_tag()
	{
		$q_edit = $this->template == 'ask' || isset( $this->content['form_q_edit'] );
		$tag_prevent = qa_opt('tagging_tools_prevent');

		if ( $q_edit && $tag_prevent )
		{
			return
				qa_get_logged_in_points() < (int) qa_opt('tagging_tools_rep') &&
				qa_get_logged_in_level() < QA_USER_LEVEL_EXPERT;
		}

		return false;
	}

}
