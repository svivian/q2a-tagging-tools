
/* ajax request to "ajax-tagging-tools" */
function ajax_retag()
{
	$.ajax({
		url: qa_root+"ajax-tagging-tools",
		success: function(response) {
			var posts_left = parseInt(response,10);
			var $ok = $("#tagging_tools_recalc");
			if (posts_left === 0) {
				$ok.text("All tags edited!");
			}
			else if (isNaN(posts_left)) {
				$ok.text("There was an error editing the tags.");
			}
			else {
				$ok.text("Editing tags... "+posts_left+" posts remaining...");
				window.setTimeout(ajax_retag, 1500);
			}

		}
	});
}

$(window).load(ajax_retag);
