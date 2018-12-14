<?php
/*
	Question2Answer Tagging Tools plugin
	Copyright (C) 2011 Scott Vivian
	License: https://www.gnu.org/licenses/gpl.html
*/

// Handle redirects from an old tag to its replacement
class qa_tagging_tools_redirect
{
	public function plugins_loaded()
	{
		$request = qa_request();
		$useRedirects = (bool) qa_opt('tagging_tools_redirect');

		if (!$useRedirects || !preg_match('#^tag/([^/]+)$#', $request, $matches))
			return;

		$currentTag = $matches[1];

		require_once 'qa-tt-helper.php';
		$tagSynonyms = qa_tt_helper::synonyms_to_array(qa_opt('tagging_tools_synonyms'));

		foreach ($tagSynonyms as $synonym) {
			if ($synonym['from'] === $currentTag && $synonym['to'] !== '') {
				header('HTTP/1.1 301 Moved Permanently');
				qa_redirect('tag/' . $synonym['to']);
				return;
			}
		}
	}
}
