
/* ajax request to 'ajax-tagging-tools' */
function ajax_retag()
{
	var langProgess = VAR_LANG_PROGESS;
	var langError = VAR_LANG_ERROR;
	var langDone = VAR_LANG_DONE;

	$.ajax({
		url: qa_root+'ajax-tagging-tools',
		success: function(response) {
			var posts_left = parseInt(response,10);
			var $ok = $('#tagging_tools_recalc');
			if (posts_left === 0) {
				$ok.text(langDone);
			}
			else if (isNaN(posts_left)) {
				$ok.text(langError);
			}
			else {
				$ok.text(langProgess.replace('^1', posts_left));
				window.setTimeout(ajax_retag, 1500);
			}

		}
	});
}

$(window).load(ajax_retag);
