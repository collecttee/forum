<?php
function template_main()
{
    global $context, $txt;

    if (!empty($context['saved_successful']))
        echo '
					<div class="infobox">', $txt['save'], '</div>';
    if (!empty($context['not_found_user']))
        echo '
					<div class="errorbox">', $txt['username_no_exist'], '</div>';
    if (!empty($context['exists']))
        echo '
					<div class="errorbox">', $txt['exists_this_user'], '</div>';
    if (!empty($context['pass-max']))
        echo '
					<div class="errorbox">', $txt['pass_max'], '</div>';
    echo '
		<div class="cat_bar">
			<h3 class="catbg">Merit Post</h3>
		</div>
		<div class="windowbg">
		You have received a total of <strong>', $context['merit_amount'], '</strong> merit. This is what determines your forum rank. You typically cannot losethis merit. You have <strong>', $context['smerit_amount'], ' </strong>sendable merit (sMerit) which you can send to other people. There is no point inhoarding sMerit: keeping it yourself does not benefit you, and we reserve the right to decay unused sMerit inthe future.
        <hr/>
        <h4>Merit Post</h4>
        <ul>
        <li>Poster:', $context['username'], '</li>
        <li>Post:', $context['title'], '</li>
        <li>Poster:<input type="number" name="amount"></li>
        <li><input type="submit" value="Send" class="button"></li>
        </ul>
		</div>';

}