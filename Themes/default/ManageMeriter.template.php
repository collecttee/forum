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
                            <form method="post" action="', $context['post_url'], '" >                                  
									<dt>
										<a id="setting_reg_verification"></a> <span><label for="reg_verification">Single Issuance Limit</label></span>
									</dt>
									<dd>
										<input type="number" name="limit" id="limit" value="', $context['limit'], '">
									</dd>
				<input type="submit" value="Save" class="button">
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="' . $context['admin-eh_token_var'] . '" value="' . $context['admin-eh_token'] . '">
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
				<input type="hidden" name="' . $context['admin-eh_token_var'] . '" value="' . $context['admin-eh_token'] . '">
				<input type="submit" value="Add" class="button">
				</form> 
								</dl>	
								</div>';
    // Go through each table!
    foreach ($context['tables'] as $table)
    {
        echo '
		<table class="table_grid report_results">';

        if (!empty($table['title']))
            echo '
			<thead>
				<tr class="title_bar">
					<th scope="col" colspan="', $table['column_count'], '">', $table['title'], '</th>
				</tr>
			</thead>
			<tbody>';

        // Now do each row!
        $row_number = 0;
        foreach ($table['data'] as $row)
        {
            if ($row_number == 0 && !empty($table['shading']['top']))
                echo '
				<tr class="windowbg table_caption">';
            else
                echo '
				<tr class="', !empty($row[0]['separator']) ? 'title_bar' : 'windowbg', '">';

            // Now do each column.
            $column_number = 0;

            foreach ($row as $data)
            {
                // If this is a special separator, skip over!
                if (!empty($data['separator']) && $column_number == 0)
                {
                    echo '
					<td colspan="', $table['column_count'], '" class="smalltext">
						', $data['v'], ':
					</td>';
                    break;
                }

                // Shaded?
                if ($column_number == 0 && !empty($table['shading']['left']))
                    echo '
					<td class="table_caption ', $table['align']['shaded'], 'text"', $table['width']['shaded'] != 'auto' ? ' width="' . $table['width']['shaded'] . '"' : '', '>
						', $data['v'] == $table['default_value'] ? '' : ($data['v'] . (empty($data['v']) ? '' : ':')), '
					</td>';
                else
                    echo '
					<td class="smalltext centertext" ', $table['width']['normal'] != 'auto' ? ' width="' . $table['width']['normal'] . '"' : '', !empty($data['style']) ? ' style="' . $data['style'] . '"' : '', '>
						', $data['v'], '
					</td>';

                $column_number++;
            }

            echo '
				</tr>';

            $row_number++;
        }
        echo '
			</tbody>
		</table>';
    }
}