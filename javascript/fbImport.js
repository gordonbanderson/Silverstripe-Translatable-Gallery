JQ = jQuery.noConflict();


// import one image at a time and recurse once the image has finished loading
function importImage(gallery_id, position) {
	var previewImage = JQ('#facebookGalleryPreview').find('div:nth-child('+position+')');

	if (JQ('#facebookGalleryPreview div').length == 0) {
		// we are done
		alert('done');
	} else {
		//alert(previewImage);
	var pid = JQ(previewImage).attr('id').split('_')[1];
	var caption = JQ(previewImage).find('.caption').html();
	var src_big = JQ(previewImage).find('img').attr('src_big');

	//alert(pid+","+caption+","+src_big);

	//alert(previewImage.html());

	//JQ(previewImage).html("Importing");
	JQ(previewImage).addClass("processing");

	




	JQ.ajax({
	  type: "POST",
	  //async: true,
	  url: 'fbimport/ImportPicture/'+gallery_id,
	  dataType: 'json',
	  data: "pid="+pid+"&src_big="+src_big+"&caption="+caption,
	  success: function(msg){
	  	
	    JQ(previewImage).addClass('processing');

	    JQ(previewImage).remove();
	    		importImage(gallery_id, 1);

	    	    //alert( "Data Saved: " + msg );

	  }
	});
	}

}

JQ(document).ready(function() {
	alert('doc ready fb import js');

				JQ('#fbImportButton').removeClass('hidden');



	JQ('.facebookImportButton').livequery('click', function() {
		var gallery_id = JQ(this).attr('id').split('_')[1];

		importImage(gallery_id, 1);
	
		//var jsonData = JQ(this).attr('fbGalleryInfo');
		

/*
		ctr = 0;
		previewImages.each(function(i) { 
    		var pid = JQ(this).attr('id').split('_')[1];
    		var caption = JQ(this).find('.caption').html();
    		var src_big = JQ(this).find('img').attr('src_big');


    		JQ.ajax({
			  type: "POST",
			  //async: true,
	    	  url: 'fbimport/ImportPicture/'+gallery_id,
	    	  dataType: 'json',
			  data: "pid="+pid+"&src_big="+src_big+"&caption="+caption,
			  success: function(msg){
			  	
			    alert( "Data Saved: " + msg );
			    JQ(this).addClass('processing');

			    JQ(this).html('Blah');
			  }
			});

			ctr = ctr + 1;



}		);
*/
		
	});

	// Triggered when the facebook ID textbox is edited.
	// 1. Check value is valid
	// 2. Load preview from facebook
	// 3. Show import button
	JQ('#Form_EditForm_FacebookAlbumID').livequery('change', function() {


		JQ('#Form_EditForm_FacebookAlbumID').addClass('loading');
		JQ('#fbImportButton').addClass('hidden');

		JQ('#facebookGalleryPreview').html("<p>Preview images will appear here</p>");
		JQ('#facebookGalleryPreview').addClass('loading');
		
		var urlOrAlbumID = JQ(this).val();

		var htmlPreview = '';

		JQ.getJSON('/fbimport/PreviewAlbum?AlbumIDOrURL='+urlOrAlbumID, function(data) {
		//alert(data['title']);

			statusMessage('Album found - loading preview images');


			for (var i = data['images'].length - 1; i >= 0; i--) {
				var image = data['images'][i];

				for (var key in image) {
				//	alert(key);
				//	alert(image['key']);
				}

				htmlPreview = htmlPreview + '<div class="fbPreviewImg" id="fbpic_'+image['pid']+'"><img src="';
				htmlPreview = htmlPreview + image['src_small']+'" ';
				//htmlPreview - htmlPreview + '" alt="'+image['src_big']+'/>" '

				htmlPreview = htmlPreview + 'src_big="'+image['src_big'];
				htmlPreview = htmlPreview + '"/>';
				htmlPreview = htmlPreview + '<div class="caption">' +image['caption'];
				htmlPreview = htmlPreview + "</div></div>\n\n";

				//alert(image['caption']);
			};

			if (data['images'].length == 0) {
				var galleryID = JQ('#Form_EditForm_FacebookAlbumID').val();
				var msg = "<p>No images were found for gallery with facebook id '"+galleryID+"'</p>";
				JQ('#facebookGalleryPreview').html(msg);

			} else {
				JQ('#facebookGalleryPreview').html(htmlPreview);
			}

			JQ('#fbImportButton').removeClass('hidden');

			JQ('#Form_EditForm_FacebookAlbumID').removeClass('loading');

			statusMessage('Album found successfully');

			JQ('#Form_EditForm_Content_ifr').contents().find('#tinymce').html('<p>'+data['description']+'</p>');  	
		  });
		
	});

});

