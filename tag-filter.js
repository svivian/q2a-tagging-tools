
function qa_tag_verify()
{
	var tagSeparator = VAR_TAG_SEPARATOR;
	var tagReqPoints = VAR_TAG_POINTS;
	var langTagError = VAR_TAG_ERROR;

	var tags = $('#tags').val().split(tagSeparator);
	var alltags = ','+qa_tags_complete.toLowerCase()+',';
	var errorShowing = $('#tags').siblings('.qa-tag-synonyms-error').length > 0;

	for (var i in tags) {
		var tag = tags[i];
		if (tag.length > 0 && alltags.indexOf(','+tag.toLowerCase()+',') == -1) {
			var errorMsg = langTagError.replace('^1', tag).replace('^2', tagReqPoints);
			var errorHtml = '<div class="qa-form-tall-error qa-tag-synonyms-error" style="display:none">'+errorMsg+'</div>';

			if (errorShowing) {
				$('.qa-tag-synonyms-error').detach();
			}
			$(errorHtml).insertAfter('#tags').slideDown().delay(8000).slideUp('fast', function() { $(this).detach() } );

			qa_hide_waiting(this);
			return false;
		}
	}

	document.ask.submit();
}

$(function(){
	$('.qa-form-tall-button-ask').on('click', qa_tag_verify);
});
