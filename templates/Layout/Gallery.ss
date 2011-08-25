<h1>$Title</h1>

$Content

<ul class="nobullets gallery">
<% control Children %>
<li class="oriented{$Photo.Orientation}">
<a id="photo_$ID" rel="prettyPhoto[pp_gal]" title="$Caption" href="$Link" class="highslide">
<% control Photo %>
<% if Orientation = 1 %>
<% control SetHeight(140) %>
<img src="$URL" alt="$Title" title="$Title" style="height:{$Height}px;width:{$Width}px;"/>
<% end_control %>
<% else %>
<% control SetHeight(140) %>
<img src="$URL"  title="$Title" alt="$Title" style="height:{$Height}px;width:{$Width}px;"/>
<% end_control %>
<% end_if %>
<% end_control %>
</a>




</li>
<% end_control %>
</ul>


<script type="text/javascript" src="themes/wot/javascript/highslide-with-gallery.js"></script>


<link rel="stylesheet" href="/silverstripe-translatable-gallery/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
<script src="/silverstripe-translatable-gallery/javascript/jquery.prettyPhoto.min.js" type="text/javascript" charset="utf-8"></script>



<script type="text/javascript">

JQ = jQuery.noConflict();


JQ(document).ready(function(){
	console.log("trying to bind pretty photo");
    JQ("a[rel^='prettyPhoto']").prettyPhoto(
    	{
    		theme: 'facebook',
    		showTitle: true,
    		opacity: 0.9,
    		custom_markup: 'wibble'
    	}
	);
    console.log("/trying to bind pretty photo");


	// change the links to these for highslide but leave as the pages for non JS
	imageURLs = {};
	<% control AllChildren %>
imageURLs['photo_$ID']=<% control Photo %><% control SetSize(900,600) %>'$URL';<% end_control %>
	<% end_control %>
	<% end_control %>





JQ('a.highslide').each(function() {
	var image_id = JQ(this).attr('id');
	console.log("IMAGE ID:"+image_id);
	var imageURL = imageURLs[image_id];
	JQ(this).attr('href', imageURL);
	console.log(imageURL);
	; 
});



});

</script>