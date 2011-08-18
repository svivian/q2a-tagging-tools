<?php

	class qa_html_theme_layer extends qa_html_theme_base {

		function option_default($option) {
			
			switch($option) {
				default:
					return false;
			}
			
		}

	// theme replacement functions

		function head_script()
		{
			qa_html_theme_base::head_script();
			if ($this->forbid_new_tag()) {

				$this->output_raw("
<script>
function qa_tag_verify()
{
	tags = jQuery('#tags').val().split(' ');
	var alltags = ','+qa_tags_complete+',';
	for(i in tags) {
		if(alltags.indexOf(','+tags[i]+',') >= 0) {
			continue;
		}
		else {
			var error = '<div class=\"qa-form-tall-error\">The tag \"'+tags[i]+'\" does not exist, and you need ".qa_opt('tag_synonyms_rep')." points to create new tags!</div>';
			jQuery('#title').insertAfter(error);
			return false;
		}
	}
	document.ask.submit();
}
</script>");
			}
		}
		
		function form_button_data($button, $key, $style)
		{
			if ($this->forbid_new_tag()) {
				if($key === 'ask') {
					
					$baseclass='qa-form-'.$style.'-button qa-form-'.$style.'-button-'.$key;
					$hoverclass='qa-form-'.$style.'-hover qa-form-'.$style.'-hover-'.$key;
					
					$this->output('<INPUT'.rtrim(' '.@$button['tags']).' onclick="qa_tag_verify();" VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="button" CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"/>');
				}
				else qa_html_theme_base::form_button_data($button, $key, $style);
			}
			else qa_html_theme_base::form_button_data($button, $key, $style);
		}
		
	// worker functions
		
		function forbid_new_tag() {
			if($this->template != 'ask' || $this->qa_state || !qa_opt('tag_synonyms_prevent')) return false;
			if(qa_get_logged_in_points()< (int)qa_opt('tag_synonyms_rep') && qa_get_logged_in_level()<QA_USER_LEVEL_EXPERT) return true;
			return false;
		}
	}

