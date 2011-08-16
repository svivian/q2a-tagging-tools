<?php
/*
	Question2Answer Tag Synonyms plugin, v1.0
	License: http://www.gnu.org/licenses/gpl.html
*/

class qa_tag_synonyms
{

	function admin_form( &$qa_content )
	{
		// process config change
		$saved = false;
		if ( qa_clicked('tag_synonyms_save_button') )
		{
			qa_opt( 'tag_synonyms', trim( qa_post_text('tag_synonyms_text') ) );
			$saved = true;
		}

		return array(
			'ok' => $saved ? 'Tag Synonyms settings saved' : null,

			'fields' => array(
				array(
					'label' => 'Tag Synonyms',
					'tags' => 'name="tag_synonyms_text"',
					'value' => qa_opt('tag_synonyms'),
					'type' => 'textarea',
					'rows' => 20,
					'note' => 'Put each pair of synonyms on a new line. <code>q2a,question2answer</code> means that a tag of <code>q2a</code> will be replaced by <code>question2answer</code>, while <code>help</code> on its own means that tag will be removed.',
				),
			),

			'buttons' => array(
				array(
					'label' => 'Save Changes',
					'tags' => 'name="tag_synonyms_save_button"',
				),
			),
		);
	}


	function process_event( $event, $userid, $handle, $cookieid, $params )
	{
		// only interested in q_post & q_edit
		if ( $event != 'q_post' && $event != 'q_edit' )
			return;

		// get config data
		$synonyms = qa_opt('tag_synonyms');
		if ( !$synonyms )
			return;

		$tags = qa_tagstring_to_tags( $params['tags'] );
		$lines = explode( "\n", trim($synonyms) );

		// build synonym array
		$search = array();
		$replace = array();
		foreach ( $lines as $line )
		{
			$items = explode( ',', $line );
			if ( !isset($items[1]) )
				$items[1] = '';

			$search[] = $items[0];
			$replace[] = $items[1];
		}

		// loop tags and synonyms: cannot use str_replace, and preg_replace is much slower
		foreach ( $tags as &$t )
		{
			foreach ( $search as $k=>$s )
			{
				if ( $t === $s )
					$t = $replace[$k];
			}
		}

		// updating content would trigger another event, so we suspend events to avoid an infinite loop
		qa_suspend_event_reports(true);
		require_once QA_INCLUDE_DIR.'qa-app-posts.php';
		qa_post_set_content( $params['postid'], $params['title'], $params['content'], $params['format'], $tags );
		qa_suspend_event_reports(false);
	}

};
