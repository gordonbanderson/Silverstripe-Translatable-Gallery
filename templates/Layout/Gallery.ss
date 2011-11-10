<% cached 'Gallery', ID, LastEdited,  Aggregate(Photograph).Max(LastEdited) %>
<h1>$Title</h1>

$Content

<ul class="nobullets gallery">
<% control Children %>
<li class="oriented{$Photo.Orientation}">
<a id="photo_$ID" rel="prettyPhoto[pp_gal]" title="$Caption" href="$Link" class="highslide">
<% control Photo %>
<% control SetHeight(140) %>
<img alt="$Top.Title" title="$Top.Title" src="$URL"  style="height:{$Height}px;width:{$Width}px;"/>
<% end_control %>
<% end_control %>
</a>




</li>
<% end_control %>
</ul>




<link rel="stylesheet" href="/silverstripe-translatable-gallery/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
<script src="/silverstripe-translatable-gallery/javascript/jquery.prettyPhoto.min.js" type="text/javascript" charset="utf-8"></script>



<script type="text/javascript">

JQ = jQuery.noConflict();


JQ(document).ready(function(){
	//console.log("trying to bind pretty photo");
    JQ("a[rel^='prettyPhoto']").prettyPhoto(
    	{
    		theme: 'facebook',
    		showTitle: true,
    		opacity: 0.9,
    		social_tools: ''
    	}
	);
    //console.log("/trying to bind pretty photo");


	// change the links to these for highslide but leave as the pages for non JS
	imageURLs = {};
	<% control AllChildren %>
imageURLs['photo_$ID']=<% control Photo %><% control SetSize(900,600) %>'$URL';<% end_control %>
	<% end_control %>
	<% end_control %>





JQ('a.highslide').each(function() {
	var image_id = JQ(this).attr('id');
	//console.log("IMAGE ID:"+image_id);
	var imageURL = imageURLs[image_id];
	JQ(this).attr('href', imageURL);
	//console.log(imageURL);
	; 
});



});

</script>
<% end_cached %>