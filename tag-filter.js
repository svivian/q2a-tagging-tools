
function qa_tag_verify()
{
	var tagSeparator = VAR_TAG_SEPARATOR;
	var tagReqPoints = VAR_TAG_POINTS;
	var langTagError = VAR_TAG_ERROR;

	var tags = $('input#tags').val().split(tagSeparator);
	var alltags = ','+qa_tags_complete.toLowerCase()+',';
	var errorShowing = $('input#tags').siblings('.qa-tag-synonyms-error').length > 0;

	for (var i in tags) {
		var tag = tags[i];
		if (tag.length > 0 && alltags.indexOf(','+tag.toLowerCase()+',') == -1) {
			var errorMsg = langTagError.replace('^1', tag).replace('^2', tagReqPoints);
			var errorHtml = '<div class="qa-form-tall-error qa-tag-synonyms-error" style="display:none">'+errorMsg+'</div>';

			if (errorShowing) {
				$('.qa-tag-synonyms-error').detach();
			}
			$(errorHtml).insertAfter('input#tags').slideDown().delay(8000).slideUp('fast', function() { $(this).detach() } );

			var showWaiting = $('.qa-form-tall-button-save', this).get(0);
			qa_hide_waiting(showWaiting);
			return false;
		}
	}
}

$(function(){
	$('input#tags').closest('form').on('submit', qa_tag_verify);
});
