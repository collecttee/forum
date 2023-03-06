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
    echo '
		<div class="cat_bar">
			<h3 class="catbg">Manage Merit</h3>
		</div>
		<p class="information">
The function administrator sets to add or delete the Merit source user list as the internal distributiongroup of sMerit.				</p>
		<div id="report_buttons">';

    echo '
		</div>';
    template_merit_menu('smerit');
    echo '<div class="half_content">
		<div class="cat_bar">
			<h3 class="catbg">
				
				<a href="http://forum02.firedao.online/index.php?action=groups;sa=requests" id="group_requests_link">sMerit Detail</a>
			</h3>
		</div>
		<div class="windowbg" id="group_requests_panel">
			<ul>
				<li>
					<strong class="smalltext">There are no open requests for group membership.</strong>
				</li>
			</ul>
		</div><!-- #group_requests_panel -->
</div><div class="half_content">
		<div class="cat_bar">
			<h3 class="catbg">
				issue sMerit
			</h3>
		</div>
		<div class="windowbg" id="group_requests_panel">
			<ul>
				<li>
				<form  method="post" action="', $context['post_url'], '" >
					<strong class="smalltext">issue amount</strong>
					<input type="number" name="amount" style="width:80px">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
					<input type="submit" value="issue" class="button">
				</form>
				</li>
			</ul>
		</div><!-- #group_requests_panel -->
</div>';
    echo '<div class="cat_bar">
			<h3 class="catbg">
				Manager Issue sMerits Records
			</h3>
		</div>';

    // Go through each table!
    echo '<form  action="' . $context['delete_url'] . '" method="post"><table class="table_grid" id="member_list">
			<thead>
				<tr class="title_bar">
					<th scope="col" id="header_member_list_id_member" class="id_member">
						 ID
					</th>
					<th scope="col" id="header_member_list_user_name" class="user_name">
					Username 
					</th>		
					<th scope="col" id="header_member_list_user_name" class="user_name">
					Amount 
					</th>
					<th scope="col" id="header_member_list_user_name" class="user_name">
					Time 
					</th>
				</tr>
			</thead>
			<tbody>';
    foreach ($context['users'] as $val) {
        echo '
				<tr class="windowbg" id="list_member_list_0">
					<td class="id_member">
						' . $val['id'] . '
					</td>
					<td class="user_name">
			        ' . $val['member_name'] . '
					</td>
					<td class="display_name">
						' . $val['amount'] . '
					</td>
					<td class="check centercol">
					' . date('Y-m-d H:i:s',$val['create_at']) . '
					</td>
				</tr>';
    }
    echo '
			</tbody>
		</table>
       
            </form>';
}