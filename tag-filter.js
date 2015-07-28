
function qa_tag_verify()
{
	var tagSeparator = VAR_TAG_SEPARATOR;
	var alltags = ","+qa_tags_complete+",";
	var errorShowing = jQuery("#tags").siblings(".qa-tag-synonyms-error").length > 0;
	var errorMsg = '<div class="qa-form-tall-error qa-tag-synonyms-error" style="display:none">The tag <q>"+tags[i]+"</q> does not exist; you need VAR_TAG_REP points to create new tags.</div>';
	var tags = $('#tags').val().split(tagSeparator);

	for (var i in tags) {
		if (tags[i].length > 0 && alltags.indexOf(","+tags[i]+",") == -1) {
			if (!errorShowing) {
				jQuery(errorMsg).insertAfter("#tags").slideDown("fast").delay(5000).slideUp("fast", function() { jQuery(this).detach() } );
			}
			qa_hide_waiting(this);
			return false;
		}
	}

	document.ask.submit();
}

$(function(){
	$('.qa-form-tall-button-ask').on('click', qa_tag_verify);
});
