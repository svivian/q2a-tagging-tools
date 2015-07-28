
function qa_tag_verify()
{
	var tagSeparator = VAR_TAG_SEPARATOR;
	var tags = $('#tags').val().split(tagSeparator);
	var alltags = ','+qa_tags_complete.toLowerCase()+',';
	var errorShowing = $('#tags').siblings('.qa-tag-synonyms-error').length > 0;

	for (var i in tags) {
		var tag = tags[i];
		if (tag.length > 0 && alltags.indexOf(','+tag.toLowerCase()+',') == -1) {
			var errorMsg = '<div class="qa-form-tall-error qa-tag-synonyms-error" style="display:none">The tag <q>'+tag+'</q> does not exist; you need VAR_TAG_REP points to create new tags.</div>';

			if (errorShowing) {
				$('.qa-tag-synonyms-error').detach();
			}
			$(errorMsg).insertAfter('#tags').slideDown().delay(8000).slideUp('fast', function() { $(this).detach() } );

			qa_hide_waiting(this);
			return false;
		}
	}

	document.ask.submit();
}

$(function(){
	$('.qa-form-tall-button-ask').on('click', qa_tag_verify);
});
