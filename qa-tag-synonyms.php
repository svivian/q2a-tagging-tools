<?php
/*
	Question2Answer Tag Synonyms plugin, v1.2
	License: http://www.gnu.org/licenses/gpl.html
*/

require_once QA_INCLUDE_DIR.'qa-db-selects.php';
require_once QA_INCLUDE_DIR.'qa-app-posts.php';

class qa_tag_synonyms
{
	// converts a string of synonyms to an array [[A,B],[C,D]]
	function _synonyms_to_array( $config )
	{
		$synonyms = array();
		$lines = explode( "\n", $config );

		foreach ( $lines as $line )
		{
			$items = explode( ',', $line );
			if ( !isset($items[1]) )
				$items[1] = '';

			$synonyms[] = array( 'from' => trim($items[0]), 'to' => trim($items[1]) );
		}

		return $synonyms;
	}

	// converts each tag to a synonym if it exists
	function _convert_tags( &$tags, &$synonyms )
	{
		$newtags = array();

		foreach ( $tags as $tag )
		{
			foreach ( $synonyms as $syn )
			{
				if ( $tag === $syn['from'] )
				{
					$tag = $syn['to'];
					break; // don't need to check more synonyms
				}
			}

			if ( $tag != '' )
				$newtags[] = $tag;
		}

		return $newtags;
	}

	function admin_form( &$qa_content )
	{
		// process config change
		$saved_msg = '';
		if ( qa_clicked('tag_synonyms_save_button') )
		{
			qa_opt( 'tag_synonyms', trim( qa_post_text('tag_synonyms_text') ) );
			qa_opt( 'tag_synonyms_prevent', qa_post_text('tag_synonyms_prevent') );
			qa_opt( 'tag_synonyms_rep', qa_post_text('tag_synonyms_rep') );

			$config = trim( qa_post_text('tag_synonyms_text') );
			qa_opt( 'tag_synonyms', $config );
			$saved_msg = 'Tag Synonyms settings saved';

			// convert all old tags based on synonyms
			if ( qa_post_text('tag_synonyms_convert') )
			{
				$synonyms = $this->_synonyms_to_array( $config );
				$edited = 0;

				qa_suspend_event_reports(true); // avoid infinite loop
				foreach ( $synonyms as $syn )
				{
					list( $questions, $qcount ) = qa_db_select_with_pending(
						qa_db_tag_recent_qs_selectspec( null, $syn['from'], 0, false, 500 ), // using 500 as $count is a bit hacky
						qa_db_tag_count_qs_selectspec( $syn['from'] )
					);

					foreach ( $questions as $q )
					{
						$oldtags = qa_tagstring_to_tags( $q['tags'] );
						$newtags = $this->_convert_tags( $oldtags, $synonyms );
						qa_post_set_content( $q['postid'], null, null, null, $newtags );
						$edited++;
					}
				}
				qa_suspend_event_reports(false);
				$saved_msg .= ' (and ' . $edited . ' tags edited)';
			}
		}


		return array(
			'ok' => $saved_msg,

			'fields' => array(
				array(
					'label' => 'Tag Synonyms',
					'tags' => 'name="tag_synonyms_text"',
					'value' => qa_opt('tag_synonyms'),
					'type' => 'textarea',
					'rows' => 20,
					'note' => 'Put each pair of synonyms on a new line. <code>q2a,question2answer</code> means that a tag of <code>q2a</code> will be replaced by <code>question2answer</code>, while <code>help</code> on its own means that tag will be removed.',
				),
				array(
					'label' => 'Also convert existing tags using above rules',
					'tags' => 'name="tag_synonyms_convert"',
					'value' => '',
					'type' => 'checkbox',
				),
				array(
					'label' => 'Prevent new users from creating new tags',
					'tags' => 'onclick="!this.checked?jQuery(\'#tSynMin\').attr(\'disabled\',true):jQuery(\'#tSynMin\').removeAttr(\'disabled\');" name="tag_synonyms_prevent"',
					'value' => qa_opt('tag_synonyms_prevent'),
					'type' => 'checkbox',
				),
				array(
					'label' => 'Min reputation to create new tags',
					'tags' => 'id="tSynMin" name="tag_synonyms_rep"',
					'value' => qa_opt('tag_synonyms_rep'),
					'type' => 'number',
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
		$config = qa_opt('tag_synonyms');
		if ( !$config )
			return;

		$oldtags = qa_tagstring_to_tags( $params['tags'] );
		$synonyms = $this->_synonyms_to_array( $config );

		$newtags = $this->_convert_tags( $oldtags, $synonyms );

		// updating content would trigger another event, so we suspend events to avoid an infinite loop
		qa_suspend_event_reports(true);
		qa_post_set_content( $params['postid'], $params['title'], $params['content'], $params['format'], $newtags );
		qa_suspend_event_reports(false);
	}

};
