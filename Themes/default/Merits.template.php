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
			<h3 class="catbg">Manage Merit</h3>
		</div>
		<p class="information">
The function administrator sets to add or delete the Merit source user list as the internal distributiongroup of sMerit.				</p>
		<div id="report_buttons">';

    echo '
		</div>';
    template_merit_menu('merit');
    echo '<div class="cat_bar">
			<h3 class="catbg">Set Merits Source User</h3>
		</div>
		<div class="windowbg">
								<dl class="settings">
								  <form method="post" action="', $context['post_url'], '" >    
									<dt>
										<a id="setting_reg_verification"></a> <span><label for="reg_verification">Add Source Users</label></span>
									</dt>
									<dd>
										<input type="text" name="username" id="recaptcha_site_key" value="">
									</dd>
			
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="submit" value="Add" class="button" style="float: right">
				</form> 
								</dl>	
								</div>';
    echo '<div class="cat_bar">
			<h3 class="catbg">
				Members List
			</h3>
		</div>';

    // Go through each table!
    echo '	<strong class="">Current Official Pool sMerit Total	 ' . $context['pool_amount'] . '</strong><form  action="' . $context['delete_url'] . '" method="post"><table class="table_grid" id="member_list">
			<thead>
				<tr class="title_bar">
					<th scope="col" id="header_member_list_id_member" class="id_member">
						Member ID
					</th>
					<th scope="col" id="header_member_list_user_name" class="user_name">
					Username 
					</th>		
						<th scope="col" id="header_member_list_user_name" class="user_name">
					Address 
					</th>	<th scope="col" id="header_member_list_user_name" class="user_name">
					sMerit 
					</th>
						<th scope="col" id="header_member_list_user_name" class="user_name">
					Transfer sMerit 
					</th>
					<th scope="col" id="header_member_list_check" class="check centercol">
						<input type="checkbox" onclick="invertAll(this, this.form);">
					</th>
					
				</tr>
			</thead>
			<tbody>';
    foreach ($context['users'] as $val) {
        $merit = $val['smerit'] ?? 0;
        echo '
				<tr class="windowbg" id="list_member_list_0">
					<td class="id_member">
						' . $val['id_member'] . '
					</td>
					<td class="user_name">
						<a href="http://forum02.firedao.online/index.php?action=profile;u=' . $val['id_member'] . '">' . $val['member_name'] . '</a>
					</td>
					<td class="display_name">
						' . $val['address'] . '
					</td><td class="display_name">
						' . $merit . '
					</td>
					<td class="user_name"><input type="number" name="amount[]"><input type="hidden" name="id_member[]" value="' . $val['id_member'] . '"></td>
					<td class="check centercol">
						<input type="checkbox" name="delete[]" value="' . $val['id'] . '">
					</td>
				</tr>';
    }
    echo '
			</tbody>
		</table>
          <div class="flow_auto">
			<div class="additional_row">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="work" value="delete">
				<input type="submit" name="delete_members" value="Delete Selected Members" style="float: right" data-confirm="Are you sure you want to delete the selected members?" class="button you_sure">
			    <input type="submit" name="transfer" value="Transfer sMerit" style="float: right" class="button">
			</div>
		</div>
            </form>';
}
function template_usersMeritTransfer(){
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
    template_merit_menu('usersMeritTransfer');

    // Go through each table!
    echo '<form  method="post"><table class="table_grid" id="member_list">
			<thead>
				<tr class="title_bar">
					<th scope="col" id="header_member_list_id_member" class="id_member">
						 ID
					</th>
					<th scope="col" id="header_member_list_user_name" class="user_name">
					From 
					</th>		
						<th scope="col" id="header_member_list_user_name" class="user_name">
					To 
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
    foreach ($context['users'] as $k=> $val) {
        $id = $k+$context['start'] + 1;
        echo '
				<tr class="windowbg" id="list_member_list_0">
					<td class="id_member">
						' . $id . '
					</td>
					<td class="user_name">
			        ' . $val['a'] . '
					</td>
					<td class="user_name">
			        ' . $val['b'] . '
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
       	<div class="pagesection">
			<div class="pagelinks floatleft">', $context['page_index'], '</div>
        </div>
            </form>';
}
function template_sMeritTransfer(){
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
    template_merit_menu('sMeritTransfer');

    // Go through each table!
    echo '<form  method="post"><table class="table_grid" id="member_list">
			<thead>
				<tr class="title_bar">
					<th scope="col" id="header_member_list_id_member" class="id_member">
						 ID
					</th>
					<th scope="col" id="header_member_list_user_name" class="user_name">
					Operator 
					</th>		
						<th scope="col" id="header_member_list_user_name" class="user_name">
					To 
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
    foreach ($context['users'] as $k=> $val) {
        $id = $k+$context['start'] + 1;
        echo '
				<tr class="windowbg" id="list_member_list_0">
					<td class="id_member">
						' .$id . '
					</td>
					<td class="user_name">
			        ' . $val['a'] . '
					</td>
					<td class="user_name">
			        ' . $val['b'] . '
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
       	<div class="pagesection">
			<div class="pagelinks floatleft">', $context['page_index'], '</div>
        </div>
            </form>';
}
function template_smerit()
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
    if (!empty($context['exceeded_maximum']))
        echo '
					<div class="errorbox">', $txt['exceeded_maximum'], '</div>';
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
					<p><strong class="smalltext">Current Official Pool sMerit:', $context['pool_amount'], '</strong></p>
					<p><strong class="smalltext">Destroyed  sMerit:', $context['total_issue'] -  $context['user_holder'] - $context['pool_amount'], '</strong></p>
					<p><strong class="smalltext">User holder sMerit:', $context['user_holder'], '</strong></p>
					<p><strong class="smalltext">Total issue sMerit:', $context['total_issue'], '</strong></p>
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
				<br/>	<br/>	<br/>
				</li>
			</ul>
		</div><!-- #group_requests_panel -->
</div>';
    echo '<div class="cat_bar">
			<h3 class="catbg">
				Manager Total Issue sMerit 
			</h3>
		</div>';

    // Go through each table!
    echo '<form  action="' . $context['delete_url'] . '" method="post"><table class="table_grid" id="member_list">
			<thead>
				<tr class="title_bar">
					<th scope="col" id="header_member_list_user_name" class="user_name">
					Username 
					</th>		
					<th scope="col" id="header_member_list_user_name" class="user_name">
					Amount 
					</th>
				</tr>
			</thead>
			<tbody>';
    foreach ($context['users_total'] as $k=> $val) {
        echo '
				<tr class="windowbg" id="list_member_list_0">
					<td class="user_name">
			        ' . $val['member_name'] . '
					</td>
					<td class="display_name">
						' . $val['amount'] . '
					</td>
				
				</tr>';
    }
    echo '
			</tbody>
		</table>
       
            </form> <br/><hr/><br/>';

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
    foreach ($context['users'] as $k => $val) {
        $id = $k + $context['start'] + 1;
        echo '
				<tr class="windowbg" id="list_member_list_0">
					<td class="id_member">
						' . $id . '
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
       	<div class="pagesection">
			<div class="pagelinks floatleft">', $context['page_index'], '</div>
        </div>
            </form>';

}
function template_systemsMerit()
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
    template_merit_menu('systemsMerit');


    // Go through each table!
    echo '<form  method="post"><table class="table_grid" id="member_list">
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
    foreach ($context['users'] as $k=> $val) {
        $id = $k+$context['start'] + 1;
        echo '
				<tr class="windowbg" id="list_member_list_0">
					<td class="id_member">
						' . $id . '
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
       	<div class="pagesection">
			<div class="pagelinks floatleft">', $context['page_index'], '</div>
        </div>
            </form>';

}

function template_emerit()
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
    template_merit_menu('emerit');


    // Go through each table!
    echo '<form  method="post"><table class="table_grid" id="member_list">
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
       	<div class="pagesection">
			<div class="pagelinks floatleft">', $context['page_index'], '</div>
        </div>
            </form>';

}