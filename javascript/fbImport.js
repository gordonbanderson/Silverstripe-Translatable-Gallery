JQ = jQuery.noConflict();


var button;
var userInfo;


/*
function facebookPrimed() {
    return FB;
}
*/


// Load the SDK Asynchronously
(function(d) {
    var js, id = 'facebook-jssdk',
        ref = d.getElementsByTagName('script')[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement('script');
    js.id = id;
    js.async = true;
    js.src = "//connect.facebook.net/en_US/all.js";
    ref.parentNode.insertBefore(js, ref);
}(document));


// this call back is called when the Facebook SDK has loaded
window.fbAsyncInit = function() {

    ////alert('fb async init called');
    //alert('fb code loaded, sync call');

    // initialise facebook
    initFacebook();


};


// initialise the FB object and check login
function initFacebook() {

    //alert('init fb');


    FB.init({
        //FIXME - remove hardwire
        appId: '158830437575352',
        //change the appId to your appId
        status: true,
        cookie: true,
        xfbml: true,
        oauth: true
    });

    //alert('init done');


    if (typeof(FB) != "undefined") {
        //alert('fb is defined T1');

        // silverstripe does not reload JS files, so use jquery live to bind to an arbitrary ID, and each
        // time the gallery admin is invoked, recheck the login against FB
        JQ('#fb-auth').livequery(function() {

            FB.getLoginStatus(function(response) {
            //alert('got fb login status');

            //alert(response.status);
            if (response.status === 'connected') {

                FB.api('/me', function(info) {
                    login(response, info);
                    //updateButton(response);
                });
                //var uid = response.authResponse.userID;
                //var accessToken = response.authResponse.accessToken;
            } else if (response.status === 'not_authorized') {
                //alert('not authed');
                // the user is logged in to Facebook, 
                //but not connected to the app
            } else {
                //alert('not logged in to facebook');
                // the user isn't even logged in to Facebook.
            }
        }, true); // force round trip
        });

        
    } else {
        //alert('type of FB undefined');
    }



    FB.getLoginStatus(function(response) {
        //alert("LOGIN STATUS:" + response.status);
        if (response.status === 'connected') {
            // the user is logged in and connected to your
            // app, and response.authResponse supplies
            // the user's ID, a valid access token, a signed
            // request, and the time the access token 
            // and signed request each expire
            var uid = response.authResponse.userID;
            var accessToken = response.authResponse.accessToken;
            //alert('T1 logged in');
        } else if (response.status === 'not_authorized') {
            // the user is logged in to Facebook, 
            //but not connected to the app
            //alert('T2 not authorized');
        } else {
            // the user isn't even logged in to Facebook.
            //alert('T3 not logged in to facebook');
        }
    });

   
    function updateButton(response) {
        //alert(response);
        //alert('update button');
        button = document.getElementById('fb-auth');
        userInfo = document.getElementById('user-info');

        ////alert('in update button');
        //////alert('updating button');
        if (response.authResponse) {
            //alert('logged in');
            //user is already logged in and connected
            FB.api('/me', function(info) {
                login(response, info);
            });

            JQ('#facebookGalleryPreview').html("<p> FB - Preview images will appear here</p>");
            // show load albums button
            JQ('.facebookImportButton').removeClass('hidden');

            button.onclick = function(e) {
                //////alert('logging out');
                FB.logout(function(response) {
                    logout(response);

                    // updateButton(response);
                });

            };
        } else {
            //alert('logged out');
            //user is not connected to your app or logged out
            button.innerHTML = 'Login 2';
            JQ('#facebookGalleryPreview').html("<p>Please login to facebook first to import images</p>");

            button.onclick = function() {
                JQ('#facebookGalleryPreview').html("<p>Logging in </p>");

                ////alert('login clicked');
                button.innerHTML = 'Logging in...';

                showLoader(true);
                FB.login(function(response) {
                    if (response.authResponse) {
                        FB.api('/me', function(info) {
                            login(response, info);
                        });
                    } else {
                        //user cancelled login or did not grant authorization
                        showLoader(false);
                    }
                }, {
                    scope: 'friends_photos,user_photos'
                });
            }

        }


    }


     //alert('T1 checked login status');

    showLoader(true);


    FB.getLoginStatus(updateButton);
    FB.Event.subscribe('auth.statusChange', updateButton);
    //alert('updating button');
}





function login(response, info) {
    //alert('attempting to log in');
    if (response.authResponse) {
        JQ('#fbImportButton').addClass('hidden');
        JQ('#FacebookAlbumID').addClass('hidden');
        JQ('#FacebookCoverPicID').addClass('hidden');
        var accessToken = response.authResponse.accessToken;


        JQ('#user-info').html('<img src="https://graph.facebook.com/' + info.id + '/picture">' + info.name);
        JQ('#fb-auth').html('Log out'); // = 'Logout';
        JQ('.facebookImportButton').removeClass('hidden');



        showLoader(false);
    } else {
        //alert('T2');
    }
}

function logout(response) {
    JQ('#user-info').html('');
    JQ('#facebookGalleryPreview').html("<p>You have logged out of facebook</p>");
    JQ('#fb-auth').html('Log In'); // = 'Logout';
}

function showLoader(visible) {
    console.log(visible);
}


function loadAlbumListing() {
    JQ('#Form_EditForm_FacebookAlbumID').addClass('loading');
    JQ('#fbImportButton').addClass('hidden');

    JQ('#facebookGalleryPreview').html("<p>Loading Albums...</p>");
    JQ('#facebookGalleryPreview').addClass('loading');

    var html = '<ul class="albumListing">';
    var pids = [];

    FB.api('/me', function(response) {
        showLoader(false);

        //http://developers.facebook.com/docs/reference/fql/user/
        //FIXME remove hardwiring

        alert('import T1');
        var fql = 'SELECT aid, cover_pid, name,created, description FROM album where owner=438372179510501 order by name desc';
        var query = FB.Data.query(fql, response.id);

        query.wait(function(rows) {
            for (var i = rows.length - 1; i >= 0; i--) {
                var album = rows[i];
                pids.push(album.cover_pid);
                html = html + "<li>";
                html = html + '<div title="' + album.name + '" cpid="' + album.cover_pid + '" id="cover_' + album.cover_pid + '">cover</div>';
                html = html + "<h2 aid='" + album['aid'] + "'>" + album['name'] + "</h2>";

                html = html + "</li>";
            };


            html = html + "</ul>";


            JQ('#facebookGalleryPreview').html(rows.length + ' albums found.  Loading cover images...');


            FB.api('/me', function(response) {
                showLoader(false);

                var quoted_pids = [];
                for (var i = 0; i < pids.length; i++) {
                    if (pids[i] != 0) {
                        // force to string
                        quoted_pids.push("'"+pids[i]+"'");
                    };
                }

                pids = quoted_pids.join(',');


                //http://developers.facebook.com/docs/reference/fql/user/
        
                var fql = "SELECT aid,pid,src_big,src_big_width,src_big_height from photo WHERE pid IN (" + pids + ")";
        alert('import T2 '+fql);
        
                var query = FB.Data.query(fql, response.id);
                query.wait(function(rows) {
                    JQ('#facebookGalleryPreview').html(html);

                    for (var i = rows.length - 1; i >= 0; i--) {
                        var pic = rows[i];
                        var img_html = '<img cpid="' + pic.pid + '" aid="' + pic.aid + '" style="width:' + pic.src_big_width / 4 + 'px;';
                        img_html = img_html + 'height:' + pic.src_big_height / 4 + 'px;" src="' + pic.src_big + '"/>';
                        JQ("#cover_" + pic.pid).html(img_html);
                        // html = html + "<li><h2 aid='"+album['aid']+"'>"+album['name']+"</h2></li>";
                    };

                    html = html + "</ul>";


                    JQ('#facebookGalleryPreview').removeClass('loading');
                    JQ('#Form_EditForm_FacebookAlbumID').removeClass('loading');

                });
            });
        });
    });

    //    jsonFQL.put("query2", "SELECT src_small from photo WHERE pid IN (SELECT cover_pid FROM #query1)");
}


// import one image at a time and recurse once the image has finished loading

function importImage(gallery_id, position) {
    var previewImage = JQ('#facebookGalleryPreview').find('div:nth-child(' + position + ')');
    //////alert(previewImage.html());
    if (JQ('#facebookGalleryPreview div').length == 0) {
        // we are done
        statusMessage('Import complete');
    } else {
        //////alert(previewImage);
        var pid = JQ(previewImage).attr('id').split('_')[1];
        var caption = JQ(previewImage).find('.caption').html();
        var src_big = JQ(previewImage).find('img').attr('src_big');
        var cover_pid = JQ(previewImage).find('img').attr('cpid');

        var isCover = (pid == cover_pid);

        //////alert("Comparing "+pid+" = "+cover_pid);
        JQ(previewImage).addClass("processing");

        JQ.ajax({
            type: "POST",
            //async: true,
            url: 'fbimport/ImportPicture/' + gallery_id,
            dataType: 'json',
            data: "pid=" + pid + "&src_big=" + src_big + "&caption=" + caption + "&cover=" + isCover,
            success: function(data) {


                var id = data['id'];
                var treeTitle = data['treeTitle'];
                var hasChildren = data['hasChildren'];
                var clazz = data['class'];
                var parentID = data['parentID'];

                var tree = $('sitetree');
                var newNode = tree.createTreeNode(id, treeTitle, clazz + hasChildren);
                var node = tree.getTreeNodeByIdx(parentID);
                if (!node) {
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

    //alert('document ready JQ');

    JQ('#fbImportButton').addClass('hidden');
    JQ('#FacebookAlbumID').addClass('hidden');
    JQ('#FacebookCoverPicID').addClass('hidden');

    JQ('#Form_EditForm_FacebookAlbumID').addClass('hidden');



    // load all of the friends albums
    JQ('.facebookLoadAlbumsButton').livequery('click', function() {
        JQ('.facebookLoadAlbumsButton').addClass('hidden');
        loadAlbumListing();
    });


    // click on an album listing to load the pictures
    JQ('.albumListing li h2').livequery('click', function() {
        var aid = JQ(this).attr('aid');
        var coverPid = JQ(this).attr('cpid');
        var title = JQ(this).html();

        JQ('#Form_EditForm_Title').val(title);
        JQ('#Form_EditForm_MenuTitle').val(title);
        JQ('#Form_EditForm_FacebookCoverPicID').val(coverPid);

        JQ('#Form_EditForm_FacebookAlbumID').val(aid);
        JQ('#Form_EditForm_FacebookAlbumID').change();
        JQ('.facebookLoadAlbumsButton').removeClass('hidden');

        var description = 'This is a description';
        var mce = JQ('#Form_EditForm_Content_ifr').contents().find('#tinymce');
        mce.html('<p>This is some test content</p>');



    });

    JQ('.albumListing li img').livequery('click', function() {
        var aid = JQ(this).attr('aid');
        var coverPid = JQ(this).attr('cpid');
        var title = JQ(this).parent().attr('title');

        //alert(title);

        JQ('#Form_EditForm_Title').val(title);
        JQ('#Form_EditForm_MenuTitle').val(title);
        JQ('#Form_EditForm_FacebookCoverPicID').val(coverPid);
        JQ('#Form_EditForm_FacebookAlbumID').val(aid);
        JQ('#Form_EditForm_FacebookAlbumID').change();

        var description = 'This is a description';
        JQ('#Form_EditForm_Content_ifr').contents().find('#tinymce').html('<p>' + description + '</p>');


        JQ('.facebookLoadAlbumsButton').removeClass('hidden');
    });


    JQ('.publishAllPicsButton').livequery('click', function() {
        JQ(this).addClass('loading');
        statusMessage('Publishing photographs for this gallery');
        var gallery_id = JQ(this).attr('id').split('_')[1];


        JQ.ajax({
            type: "POST",
            //async: true,
            url: 'fbimport/PublishAllPhotos/' + gallery_id,
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
                        if (node && node.parentTreeNode) {
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
                //////alert('error');
                JQ('.publishAllPicsButton').removeClass('loading');

                errorMessage("An error occurred: " + textStatus + ' ' + errorThrown);
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

        var cpid = JQ('#Form_EditForm_FacebookCoverPicID').val();
        //////alert("CPID:"+cpid);
        FB.api('/me', function(response) {
            showLoader(false);

            //http://developers.facebook.com/docs/reference/fql/user/
            var fql = "SELECT pid, src, src_small, src_big, caption FROM photo WHERE aid = '" + urlOrAlbumID + "'  ORDER BY created DESC"; // limit 4";
            var query = FB.Data.query(fql, response.id);

            var title = 'Album ' + urlOrAlbumID;

            var htmlPreview = ''; //<h2>'+title+'</h2>';
            query.wait(function(data) {
                for (var i = data.length - 1; i >= 0; i--) {
                    var image = data[i];
                    htmlPreview = htmlPreview + '<div class="fbPreviewImg" id="fbpic_' + image['pid'] + '"><img src="';
                    htmlPreview = htmlPreview + image['src_small'] + '" ';
                    //htmlPreview - htmlPreview + '" alt="'+image['src_big']+'/>" '
                    htmlPreview = htmlPreview + 'src_big="' + image['src_big'];
                    htmlPreview = htmlPreview + '" cpid="' + cpid + '"/>';
                    htmlPreview = htmlPreview + '<div class="caption">' + image['caption'];
                    htmlPreview = htmlPreview + "</div></div>\n\n";
                };


                JQ('#facebookGalleryPreview').html(htmlPreview);
                JQ('#facebookGalleryPreview').removeClass('loading');
                JQ('#Form_EditForm_FacebookAlbumID').removeClass('loading');

                if (data.length == 0) {
                    var galleryID = JQ('#Form_EditForm_FacebookAlbumID').val();
                    var msg = "<p>No images were found for gallery with facebook id '" + galleryID + "'</p>";
                    JQ('#facebookGalleryPreview').html(msg);
                    errorMessage('No images found');

                } else {
                    JQ('#facebookGalleryPreview').html(htmlPreview);
                    statusMessage('Images found');
                    JQ('#fbImportButton').removeClass('hidden');
                }


                JQ('#Form_EditForm_FacebookAlbumID').removeClass('loading');


                JQ('#Form_EditForm_Content_ifr').contents().find('#tinymce').html('<p>' + data['description'] + '</p>');


            });
        });
        /*
        var session_key = getFacebookSessionKey();
        JQ.getJSON('/fbimport/PreviewAlbum?AlbumIDOrURL='+urlOrAlbumID+'&'+session_key, function(data) {
            ////////alert(data['title']);

            statusMessage('Album found - loading preview images');


            for (var i = data['images'].length - 1; i >= 0; i--) {
                var image = data['images'][i];

                for (var key in image) {
                    //  //////alert(key);
                    //  //////alert(image['key']);
                }

               
                ////////alert(image['caption']);
            };

            
            https://www.facebook.com/media/set/?set=a.2592468685530.132592.1069035736&type=1

            2592468685530_132592
            1069035736_132592
            */

    });


});