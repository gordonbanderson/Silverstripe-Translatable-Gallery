JQ = jQuery.noConflict();

JQ(document).ready(function() {
	alert('doc ready fb import js');

				JQ('#fbImportButton').removeClass('hidden');



	JQ('.facebookImportButton').livequery('click', function() {
		var gallery_id = JQ(this).attr('id').split('_')[1];
		var jsonData = JQ(this).attr('fbGalleryInfo');
		
		var previewImages = JQ('#facebookGalleryPreview').children();

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
		
	});

	// Triggered when the facebook ID textbox is edited.
	// 1. Check value is valid
	// 2. Load preview from facebook
	// 3. Show import button
	JQ('#Form_EditForm_FacebookAlbumID').livequery('change', function() {


		JQ('#Form_EditForm_FacebookAlbumID').addClass('loading');
		JQ('#fbImportButton').addClass('hidden');

		JQ('#facebookGalleryPreview').html("Preview images will appear here");
		
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

			JQ('#fbImportButton').removeClass('hidden');

			JQ('#facebookGalleryPreview').html(htmlPreview);
			JQ('#Form_EditForm_FacebookAlbumID').removeClass('loading');

			statusMessage('Album found successfully');

			JQ('#Form_EditForm_Content_ifr').contents().find('#tinymce').html('<p>'+data['description']+'</p>');  	
		  });
		
	});

});

