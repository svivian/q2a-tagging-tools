
Tagging Tools plugin for Question2Answer
=================================================

This is an event plugin for popular open source Q&A platform, [Question2Answer](http://www.question2answer.org). It allows you to select 'tag synonyms' - unwanted or duplicate tags that should be changed or removed.

For example if you had `websites` as a tag, some users may tag a question with `website` instead. With this plugin you can set `website` to always be changed to `websites` when a user enters it.

Other features include min/max tag length and redirecting old tag pages to the new synonym.



Pay What You Like
-------------------------------------------------

Most of my code is released under the open source GPLv3 license, and provided with a 'Pay What You Like' approach. Feel free to download and modify the code to suit your needs, and I hope you value it enough to make a small donation - any amount is welcome.

### [Donate here](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4R5SHBNM3UDLU&source=url)



Installation & Usage
-------------------------------------------------

1. Download and extract the files to a subfolder such as `tagging-tools` inside the `qa-plugins` folder of your Q2A installation. Check the [releases page](https://github.com/svivian/q2a-tagging-tools/releases) for the latest official version.

2. If your site is a different language from English, copy `qa-tt-lang-default.php` to the required language code (e.g. `qa-tt-lang-de.php` for German) and edit the phrases for your language.

3. Log in to your Q2A site as an Administrator and head to Admin > Plugins.

4. Under "Tagging Tools", enter each pair of tags on a new line, separated by a comma. For example, `q2a,question2answer` (without quotes) means that a tag of `q2a` will be replaced by `question2answer`, while `help` on its own line means that tag will be removed. Save the changes and all future questions will replace your chosen tag synonyms.

5. If you have existing mistagged questions, tick the checkbox to replace all tags in older questions with your synonyms.
   WARNING: if you have a lot of questions on your site, converting all the old questions will take a long time. It's recommended that you add tag synonyms a handful at a time to avoid too much overhead.

6. The minimum/maximum length options do as the name suggests - prevent users from entering tags that are too long or too short.

7. The new tags option limits users under a point threshold to using existing tags only. On the front-end it adds a JavaScript barrier, which for performance reasons only checks against the top tags (number as determined by the `QA_DB_RETRIEVE_COMPLETE_TAGS` constant) that are used for auto-complete. The server-side filter checks against all tags, i.e. if JS is turned off the user can input a tag not in the most popular but it must already exist.

8. The redirects option will add 301 redirects from old tag pages to new tag pages. For example if you change `q2a` to `question2answer` then the page `example.com/tag/q2a` would now be blank, so this option will redirect the page to `example.com/tag/question2answer`. If you use this feature, make sure to convert all existing tag synonyms! Otherwise if you still have some questions with the old tags, those tag pages will not be viewable any more.
