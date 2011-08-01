<%  cached 'GalleryFolder', Aggregate(Gallery).Max(LastEdited), Aggregate(GalleryFolder).Max(LastEdited), Aggregate(Photograph).Max(LastEdited), ID, LastEdited %>


<h1 class="head">$Title</h1>
$Content

<span class="txt">

<ul class="nobullets galleryFolder">

<% control Children %>
<div class="fc_col">
<% if ClassName = GalleryFolder %>

<a href="$Link" title="$Title"> 
$CoverPhoto.setSize(207,130)
</a>
<h1>$Title</h1>
$Content.Summary(60)

<% else %>

<a href="$Link" title="$Title"> 
<% control FirstPhotograph %>

<% control Photo %>
<% control setSize(207,130) %>
<img src="$URL" alt="Cover Image $Title" style="width:{$Width}px;height:{$Height}px;"/>
<% end_control %>
<% end_control %>
<% end_control %>
</a>

<h1>$Title</h1>
$Content.Summary

<% end_if %>

</div>
<% end_control %>
</ul>



<% include Pagination %>



</span><!--/txt-->

<% end_cached %>
