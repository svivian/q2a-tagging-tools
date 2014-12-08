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
			$js = array(
				'<script>',
				'function qa_tag_verify()',
				'{',
				'	var tags = jQuery("#tags").val().split(" ");',
				'	var alltags = ","+qa_tags_complete+",";',
				'	var errorShowing = jQuery("#tags").siblings(".qa-tag-synonyms-error").length > 0;',

				'	for (var i in tags) {',
				'		if (tags[i].length > 0 && alltags.indexOf(","+tags[i]+",") == -1) {',
				'			if (!errorShowing) {',
				'				var error = "<div style=\\"display:none\\" class=\\"qa-form-tall-error qa-tag-synonyms-error\\">The tag <q>"+tags[i]+"</q> does not exist; you need ' . number_format( qa_opt('tagging_tools_rep') ) . ' points to create new tags.</div>";',
				'				jQuery(error).insertAfter("#tags").slideDown("fast").delay(5000).slideUp("fast", function() { jQuery(this).detach() } );',
				'			}',
				'			qa_hide_waiting(this);',
				'			return false;',
				'		}',
				'	}',

				'	document.ask.submit();',
				'}',

				'$(function(){',
				'	$(".qa-form-tall-button-ask").on("click", qa_tag_verify);',
				'});',
				'</script>',
			);

			$this->output_raw( implode("\n", $js)."\n" );
		}
	}



	private function forbid_new_tag()
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
