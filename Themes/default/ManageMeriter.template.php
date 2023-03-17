<?php
function template_main()
{
    global $context, $txt;

    if (!empty($context['saved_successful']))
        echo '
					<div class="infobox">', $txt['settings_saved'], '</div>';
    if (!empty($context['not_found_user']))
        echo '
					<div class="errorbox">', $txt['hooks_missing'], '</div>';
    if (!empty($context['exists']))
        echo '
					<div class="errorbox">', $txt['hooks_active'], '</div>';
    echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_title'], '</h3>
		</div>
		<p class="information">
Edit your Merit function here.					</p>
		<div id="report_buttons">';

    echo '
		</div>';
    echo '<div class="cat_bar">
			<h3 class="catbg">Set Merit Function Managers</h3>
		</div>
		<div class="windowbg">
		<dl class="settings">
                            <form method="post" action="', $context['pause_url'], '" >                                  
									<dt>
										<a id="setting_reg_verification"></a> <span><label for="reg_verification">Enable Merit Function</label></span>
									</dt>
									<dd>
									Pause
									<input type="checkbox" ', $context['pause'] == 1 ? "checked" : "", ' name="pause"  value="1">
									</dd>
				<input type="submit" value="Save" class="button">
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				</form> 
								</dl>
								<hr>
<dl class="settings">
                            <form method="post" action="', $context['post_url'], '" >                                  
									<dt>
										<a id="setting_reg_verification"></a> <span><label for="reg_verification">Single Issuance Limit</label></span>
									</dt>
									<dd>
										<input type="number" name="limit" id="limit" value="', $context['limit'], '">
									</dd>
				<input type="submit" value="Save" class="button">
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				</form> 
								</dl>	
								<hr>
								<dl class="settings">
								  <form method="post" action="', $context['set_url'], '" >    
									<dt>
										<a id="setting_reg_verification"></a> <span><label for="reg_verification">Set Merit Function Managers</label></span>
									</dt>
									<dd>
										<input type="text" name="username" id="recaptcha_site_key" value="">
									</dd>
			
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="submit" value="Add" class="button">
				</form> 
								</dl>	
								</div>';
    echo '<div class="cat_bar">
			<h3 class="catbg">
				Members List
			</h3>
		</div>';

    // Go through each table!
   echo '<form  action="' . $context['delete_url'] . '" method="post"><table class="table_grid" id="member_list">
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
					</th>
					<th scope="col" id="header_member_list_check" class="check centercol">
						<input type="checkbox" onclick="invertAll(this, this.form);">
					</th>
				</tr>
			</thead>
			<tbody>';
			foreach ( $context['users'] as $val) {
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
					</td>
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
				<input type="submit" name="delete_members" value="Delete Selected Members" data-confirm="Are you sure you want to delete the selected members?" class="button you_sure">
			</div>
		</div>
            </form>';
}