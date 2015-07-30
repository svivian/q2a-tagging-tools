<?php
/*
	Question2Answer Edit History plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

return array(

	// client-side
	'tag_not_usable_js' => 'The tag <q>^1</q> does not exist; you need ^2 points to create new tags.',
	// server-side
	'tags_not_usable' => 'You need ^1 points to create new tags. The following tags are not allowed: ^2',

	'admin_saved' => 'Tag Synonyms settings saved',
	'admin_synonyms' => 'Tag Synonyms',
	'admin_synonyms_note' => 'Put each pair of synonyms on a new line. <code>q2a,question2answer</code> means that a tag of <code>q2a</code> will be replaced by <code>question2answer</code>, while <code>help</code> on its own means that tag will be removed.',
	'admin_convert' => 'Also convert existing tags using above rules',
	'admin_prevent' => 'Prevent new users from creating new tags',
	'admin_minpoints' => 'Minimum reputation to create new tags',

	'recalc_start' => 'Editing tags...',
	'recalc_progress' => 'Editing tags... ^1 posts remaining...',
	'recalc_error' => 'There was an error editing the tags.',
	'recalc_done' => 'All tags edited!',

);
