<div class="wrap">
	<?php screen_icon(); ?>
	<h2>AdSense Hero Settings</h2>
<iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fwhatniche&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=true&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>
	<br /><a href="http://forums.whatniche.com/" target="_blank">Support Forums</a>
	<form method="post" action="" id="ahero_form">
		<input type="hidden" name="ahero_options" value="1" />
		<div id="ahero_items">
		<?php foreach($ads as $adnum => $ad) { ?>
			<div class="ahero_item">
				<h3 class="title">Ad #<?php echo $adnum+1; ?></h3>
				<table class="form-table">
					<tr>
						<th>
							Ad Code<br />
							<small>May be your AdSense HTML or any other HTML</small>
						</th>
						<td>
							<textarea name="ahero_code[]" cols="60" rows="10"><?php echo esc_textarea($ad['code']); ?></textarea>
						</td>
					</tr>
					<tr>
						<th>
							Location<br />
							<small>Where do you want this Ad to show?</small>
						</th>
						<td>
							<select name="ahero_loc[]">
							<?php
							foreach(array('before_content',
											'after_content',
											'before_paragraph',
											'after_paragraph',
											'random_paragraph',
											'float_left',
											'float_right',
									) as $option)
							{
							?>
								<option<?php echo ($option == $ad['loc']) ? ' selected' : ''; ?>><?php echo $option; ?></option>
							<?php
							}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<th>
							Paragraph Number<br />
							<small>Only if before/after_para was selected</small>
						</th>
						<td>
						<input type="text" name="ahero_para[]" maxlength="2" value="<?php echo isset($ad['para']) ? esc_attr($ad['para']) : ''; ?>" />
						</td>
					</tr>
					<tr>
						<th>
							Exclude Referrers<br />
							<small>Comma-separated list of referral domains to hide ads for</small>
						</th>
						<td>
						<input type="text" name="ahero_refex[]" value="<?php echo isset($ad['refex']) ? esc_attr($ad['refex']) : ''; ?>" />
						</td>
					</tr>
					<tr>
						<th>
							Exclude Categories<br />
							<small>Comma-separated list of categories to hide ads for</small>
						</th>
						<td>
						<input type="text" name="ahero_catex[]" value="<?php echo isset($ad['catex']) ? esc_attr($ad['catex']) : ''; ?>" />
						</td>
					</tr>
					<tr>
						<th>
							Include Categories Only<br />
							<small>Comma-separated list of categories this ad will show in. Hidden in all others.</small>
						</th>
						<td>
						<input type="text" name="ahero_catin[]" value="<?php echo isset($ad['catin']) ? esc_attr($ad['catin']) : ''; ?>" />
						</td>
					</tr>
					<tr>
						<th>
							Delete This Ad<br />
							<small>Ad will be removed permanently after clicking Save</small>
						</th>
						<td>
							<button onclick="ahero_delete_ad(this); return false;" class="button">Delete Ad</button>
						</td>
					</tr>
				</table>
			</div>
		<?php } ?>
		</div>
		<h3 class="title">Save</h3>
		<button onclick="ahero_add_ad(); return false;" class="button">Create New Ad</button>
		<input type="submit" class="button" name="ahero_clear" value="Clear Ads" />
		<input type="submit" class="button button-primary" value="Save" />
	</form>
</div>
<script type="text/javascript">
function ahero_add_ad()
{
	var root = document.getElementById('ahero_form');
	var items = root.getElementsByTagName('div');
	var node = root.getElementsByClassName('ahero_item')[0].cloneNode(true);
	node.getElementsByTagName('h3')[0].innerHTML = node.getElementsByTagName('h3')[0].innerHTML.replace(/#\d+/, '#' + items.length);
	var elements = node.getElementsByTagName('textarea');
	for(var i = 0; i < elements.length; i++)
	{
		elements[i].value = '';
	}
	elements = node.getElementsByTagName('input');
	for(var i = 0; i < elements.length; i++)
	{
		if(elements[i].type != 'text')
			continue;
		elements[i].value = '';
	}
	document.getElementById('ahero_items').appendChild(node);
}
function ahero_delete_ad(node)
{
	var root = node.parentNode;
	var items = document.getElementById('ahero_form').getElementsByClassName('ahero_item');
	if(items.length < 2)
	{
		document.getElementById('ahero_form').reset();
		var form = document.getElementById('ahero_form');
		var elements = form.getElementsByTagName('textarea');
		for(var i = 0; i < elements.length; i++)
		{
			elements[i].value = '';
		}
		elements = form.getElementsByTagName('input');
		for(var i = 0; i < elements.length; i++)
		{
			if(elements[i].type != 'text')
				continue;
			elements[i].value = '';
		}
		return;
	}
	while(root.className != 'ahero_item')
	{
		root = root.parentNode;
	}
	root.parentNode.removeChild(root);
	for(var i = 0; i < items.length; i++)
	{
		items[i].getElementsByTagName('h3')[0].innerHTML = items[i].getElementsByTagName('h3')[0].innerHTML.replace(/#\d+/, '#' + (i+1));
	}
}
</script>
