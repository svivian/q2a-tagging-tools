
function taggingToolsVerify()
{
	var tags = $('input#tags').val().split(taggingTools.separator);
	var alltags = ',' + qa_tags_complete.toLowerCase() + ',';

	for (var i in tags) {
		var tag = tags[i];

		if (tag.length < taggingTools.minLength || (taggingTools.maxLength > 0 && tag.length > taggingTools.maxLength)) {
			var errorMsg = taggingTools.lengthErrorTemplate
				.replace('^1', taggingTools.minLength)
				.replace('^2', taggingTools.maxLength);
			taggingToolsShowError(this, errorMsg);
			return false;
		}

		if (tag.length > 0 && alltags.indexOf(','+tag.toLowerCase()+',') == -1) {
			var errorMsg = taggingTools.newTagErrorTemplate
				.replace('^1', tag)
				.replace('^2', taggingTools.points);
			taggingToolsShowError(this, errorMsg);
			return false;
		}
	}
}

function taggingToolsShowError(askForm, errorMsg)
{
	var errorHtml = '<div class="qa-form-tall-error qa-tag-synonyms-error" style="display:none">'+errorMsg+'</div>';
	var errorShowing = $('input#tags').siblings('.qa-tag-synonyms-error').length > 0;

	if (errorShowing) {
		$('.qa-tag-synonyms-error').detach();
	}
	$(errorHtml).insertAfter('input#tags').slideDown().delay(8000).slideUp('fast', function() { $(this).detach() } );

	var showWaiting = $('.qa-form-tall-button-ask, .qa-form-tall-button-save', askForm).get(0);
	qa_hide_waiting(showWaiting);
}

$(function() {
	$('input#tags').closest('form').on('submit', taggingToolsVerify);
});
