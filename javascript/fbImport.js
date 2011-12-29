JQ = jQuery.noConflict();


function loadAlbumListing() {
    JQ('#Form_EditForm_FacebookAlbumID').addClass('loading');
    JQ('#fbImportButton').addClass('hidden');

    JQ('#facebookGalleryPreview').html("<p>Loading Albums</p>");
    JQ('#facebookGalleryPreview').addClass('loading');

    //ListAlbums
    JQ.ajax({
      type: "GET",
      //async: true,
      url: 'fbimport/ListAlbums/',
      dataType: 'json',
      //data: "pid="+pid+"&src_big="+src_big+"&caption="+caption,
      success: function(albums) {
        JQ('#facebookGalleryPreview').html("<p>Albums loaded</p>");
        JQ('#facebookGalleryPreview').removeClass('loading');
        var html = '<ul class="albumListing">';
        for (var i = albums['fql'].length - 1; i >= 0; i--) {
            
            var album = albums['fql'][i];
            html = html + "<li><h2 aid='"+album['aid']+"'>"+album['name']+"</h2></li>";

        };

        html = html + "</ul>";
        JQ('#facebookGalleryPreview').html(html);

        JQ('.facebookLoadAlbumsButton').val('Reload Albums');



    },
    error: function(jqXHR, textStatus, errorThrown) {
        errorMessage("An error occurred: '$textStatus+' ' + $errorThrown'");
    }
});
}


// import one image at a time and recurse once the image has finished loading
function importImage(gallery_id, position) {
    var previewImage = JQ('#facebookGalleryPreview').find('div:nth-child('+position+')');

    if (JQ('#facebookGalleryPreview div').length == 0) {
        // we are done
    } else {
        //alert(previewImage);
        var pid = JQ(previewImage).attr('id').split('_')[1];
        var caption = JQ(previewImage).find('.caption').html();
        var src_big = JQ(previewImage).find('img').attr('src_big');

        JQ(previewImage).addClass("processing");
        
        JQ.ajax({
          type: "POST",
          //async: true,
          url: 'fbimport/ImportPicture/'+gallery_id,
          dataType: 'json',
          data: "pid="+pid+"&src_big="+src_big+"&caption="+caption,
          success: function(data){

            var id = data['id'];
            var treeTitle = data['treeTitle'];
            var hasChildren = data['hasChildren'];
            var clazz = data['class'];
            var parentID = data['parentID'];

            var tree = $('sitetree');
            var newNode = tree.createTreeNode(id, treeTitle, clazz+hasChildren);
            var node = tree.getTreeNodeByIdx(parentID);
            if(!node) {
                node = tree.getTreeNodeByIdx(0);
            }
            node.open();
            node.appendTreeNode(newNode);
            
            JQ(previewImage).addClass('processing');

            JQ(previewImage).remove();
            importImage(gallery_id, 1);
        }
    });
}

}

JQ(document).ready(function() {
    alert('doc ready fb import js');

    JQ('#fbImportButton').addClass('hidden');

    JQ('.facebookLoadAlbumsButton').livequery('click', function() {
        JQ('.facebookLoadAlbumsButton').addClass('hidden');
        loadAlbumListing();
    });


    // click on an album listing to load the pictures
    JQ('.albumListing li h2').livequery('click', function() {
        var aid = JQ(this).attr('aid');
        JQ('#Form_EditForm_FacebookAlbumID').val(aid);
        JQ('#Form_EditForm_FacebookAlbumID').change();
        JQ('.facebookLoadAlbumsButton').removeClass('hidden');

    });


    

    JQ('.publishAllPicsButton').livequery('click', function() {
        JQ(this).addClass('loading');
        statusMessage('Publishing photographs for this gallery');
        var gallery_id = JQ(this).attr('id').split('_')[1];

    
    JQ.ajax({
      type: "POST",
      //async: true,
      url: 'fbimport/PublishAllPhotos/'+gallery_id,
      dataType: 'json',
      //data: "pid="+pid+"&src_big="+src_big+"&caption="+caption,
      success: function(data) {
        var st = $('sitetree');
        for (var i = data.length - 1; i >= 0; i--) {
            var photo = data[i];
            $('Form_EditForm').updateStatus(photo['status']);
            if (photo['stageURL'] || photo['liveURL']) {
                st.setNodeTitle(photo['id'], photo['title']);
            } else {
                var node = st.getTreeNodeByIdx(photo['id']);
                if(node && node.parentTreeNode) {
                    node.parentTreeNode.removeTreeNode(node);
                }

                JQ('Form_EditForm').reloadIfSetTo(photo['id']);    
            }

            $('Form_EditForm').elements.StageURLSegment.value = photo['stageURL'];
            $('Form_EditForm').elements.LiveURLSegment.value = photo['liveURL'];
            $('Form_EditForm').notify('PagePublished', $('Form_EditForm').elements.ID.value);
            
        };
            JQ('.publishAllPicsButton').removeClass('loading');



        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('error');
            JQ('.publishAllPicsButton').removeClass('loading');

            errorMessage("An error occurred: "+textStatus+' ' + errorThrown);
        }
    });

        


    });


    


    JQ('.facebookImportButton').livequery('click', function() {
        var gallery_id = JQ(this).attr('id').split('_')[1];

        importImage(gallery_id, 1);
        
        
    });

    // Triggered when the facebook ID textbox is edited.
    // 1. Check value is valid
    // 2. Load preview from facebook
    // 3. Show import button
    JQ('#Form_EditForm_FacebookAlbumID').livequery('change', function() {

        JQ('#fbImportButton').addClass('hidden');
        JQ('.facebookLoadAlbumsButton').removeClass('hidden');

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
                    //  alert(key);
                    //  alert(image['key']);
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

            /*
            https://www.facebook.com/media/set/?set=a.2592468685530.132592.1069035736&type=1

            2592468685530_132592
            1069035736_132592
            */

            if (data['images'].length == 0) {
                var galleryID = JQ('#Form_EditForm_FacebookAlbumID').val();
                var msg = "<p>No images were found for gallery with facebook id '"+galleryID+"'</p>";
                JQ('#facebookGalleryPreview').html(msg);
                errorMessage('No images found');

            } else {
                JQ('#facebookGalleryPreview').html(htmlPreview);
                statusMessage('Images found');
                JQ('#fbImportButton').removeClass('hidden');
            }


            JQ('#Form_EditForm_FacebookAlbumID').removeClass('loading');


            JQ('#Form_EditForm_Content_ifr').contents().find('#tinymce').html('<p>'+data['description']+'</p>');    
        });
        
    });

});

