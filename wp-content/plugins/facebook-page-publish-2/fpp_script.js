/*
	Facebook Page Publish
	By: Dean Williams
	URL: http://software.resplace.net/WordPress/facebook-page-publish
*/

jQuery(document).ready(function() {
	
	jQuery('#upload_image_button').click(function() {
        formfield = jQuery('#upload_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
	});
	
	window.send_to_editor = function(html) {
        imgurl = jQuery('img',html).attr('src');
        jQuery('#upload_image').val(imgurl);
        tb_remove();
	}
	
});

jQuery.fn.show_object_id_list = function(anchor_id, profile_access_token) {
	
	$j = jQuery.noConflict();
	dropdown = $j(this);
	anchor = $j(anchor_id);
	
	var url = "https://graph.facebook.com/me?callback=?&access_token=" + profile_access_token;
	$j.getJSON(url, function(json) {
		field = $j("<div>");
		field.css('display', 'none');
		field.css('cursor','pointer');
		field.hover(function(){
			$j(this).css('color', 'gray');
			},function(){
			$j(this).css('color', 'black');
		});
		field.html(json.name + " <em>Profile</em>");
		field.click(function() {anchor.val(json.id)});
		dropdown.append(field);
		dropdown.show();
		field.show(400);
		
	});
	
	var url = "https://graph.facebook.com/me/accounts?callback=?&access_token=" + profile_access_token;
	$j.getJSON(url, function(json) {
		$j.each(json.data, function(i, fb) {
			
			field = $j("<div>");
			field.css('display', 'none');
			field.css('cursor','pointer');
			field.hover(function(){
				$j(this).css('color', 'gray');
				},function(){
				$j(this).css('color', 'black');
			});
			field.html(fb.name + " <em>" + fb.category + "</em>");
			field.click(function() {anchor.val(fb.id)});
			dropdown.append(field);
			dropdown.show();
			field.show(400);
			
		});
		
	});
};		