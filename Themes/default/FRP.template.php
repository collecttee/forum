<?php


function template_frptransfer(){
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
			<h3 class="catbg">Manage FLM</h3>
		</div>
		<p class="information">
The function administrator sets to add or delete the Merit source user list as the internal distributiongroup of sFLM.				</p>
		<div id="report_buttons">';

    echo '
		</div>';
    template_flm_menu('frpTransfer');

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
function template_frp()
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
			<h3 class="catbg">Manage FRP</h3>
		</div>
		<p class="information">
The function administrator sets to add or delete the FRP source user list as the internal distributiongroup of FRP.				</p>
		<div id="report_buttons">';

    echo '
		</div>';
    template_frp_menu('frp');
    echo '<div class="half_content">
		<div class="cat_bar">
			<h3 class="catbg">
				
				<a href="" id="group_requests_link">FRP Detail</a>
			</h3>
		</div>
		<div class="windowbg" id="group_requests_panel">
			<ul>
				<li>
					<p><strong class="smalltext">Current Official Pool FRP:', $context['pool_amount'], '</strong></p>
			        <p><strong class="smalltext">User holder FRP:', $context['user_holder'], '</strong></p>
					<p><strong class="smalltext">Total issue FRP:', $context['total_issue'], '</strong></p>
				</li>
			</ul>
		</div><!-- #group_requests_panel -->
</div><div class="half_content">
		<div class="cat_bar">
			<h3 class="catbg">
				issue FRP
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
				Manager Total Issue sFLM 
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
				Manager Issue sFLMS Records
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

