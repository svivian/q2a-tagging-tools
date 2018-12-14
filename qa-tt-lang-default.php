<?php
/*
	Question2Answer Tagging Tools plugin
	Copyright (C) 2011 Scott Vivian
	License: https://www.gnu.org/licenses/gpl.html
*/

return [

	'admin_convert' => 'Also convert existing tags using above rules',
	'admin_min_points' => 'Minimum reputation to create new tags',
	'admin_min_tag_length' => 'Minimum tag length',
	'admin_max_tag_length' => 'Maximum tag length',
	'admin_max_tag_length_note' => '(0 = no limit)',
	'admin_prevent' => 'Prevent new users from creating new tags',
	'admin_redirect' => 'Add 301 (permanent) redirects for tag synonyms',
	'admin_synonyms' => 'Tag Synonyms',
	'admin_synonyms_note' => 'Put each pair of synonyms on a new line. `q2a,question2answer` means that a tag of `q2a` will be replaced by `question2answer`, while `help` on its own means that tag will be removed.',

	'recalc_done' => 'All tags edited!',
	'recalc_error' => 'There was an error editing the tags.',
	'recalc_progress' => 'Editing tags... ^1 posts remaining...',
	'recalc_start' => 'Editing tags...',

	// client-side
	'tag_not_usable_js' => 'The tag <q>^1</q> does not exist; you need ^2 points to create new tags.',
	'tag_bad_length_js' => 'Tags must be between ^1 and ^2 letters long.',
	// server-side
	'tags_not_usable' => 'You need ^1 points to create new tags. The following tags are not allowed: ^2',
	'tag_bad_length' => 'Tags must be between ^1 and ^2 letters long. The following tags are not allowed: ^3',

];
