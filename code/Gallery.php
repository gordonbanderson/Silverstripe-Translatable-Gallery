<?php

//require_once "facebook.php";

class Gallery extends Page {
  static $db = array (
    'BulkTitle' => 'Varchar',
    'BulkCaption' => 'Varchar',
    'BulkCopyright' => 'Varchar',
    'BulkLicense' => 'Varchar',
    'FacebookAlbumID' => 'Varchar'
  );

  // facebook information

  // the id of the facebook application
  protected static $facebook_application_id = null;

  // the secret of the facebook application
  protected static $facebook_application_secret = null;

  // user id to search
  protected static $facebook_user_id = null;


 
  static $has_many = array (
    'AttachedFiles' => 'ImageFile'
  );

  //Uncle Cheese hack from http://silverstripe.org/data-model-questions/show/6805 << to detect multiple calls to on after write or on before write
  static $has_written = false;


  function CanUseFacebook() {
    $result = (self::$facebook_application_id != null);
    $result = $result && (self::$facebook_application_secret != null);
    $result = $result && (self::$facebook_user_id != null);
    return $result;
  }

  static function setFacebookApplicationID($new_app_id) {
    return self::$facebook_application_id = $new_app_id;
  }
  
  static function setFacebookApplicationSecret($new_sec) {
    return self::$facebook_application_secret = $new_sec;
  }

  static function setFacebookUserID($new_user_id) {
    return self::$facebook_user_id = $new_user_id;
  }


  static function getFacebookApplicationID() {
    return self::$facebook_application_id;
  }

   static function getFacebookApplicationSecret() {
    return self::$facebook_application_secret;
  }
  
   static function getFacebookUserID() {
    return self::$facebook_user_id;
  }








   function getFirstPhotograph() { 
      $result = null;
      //DEBUGLOG("Get first child T1");
      if($children = $this->Children()) {
            //DEBUGLOG("Get first child T2");
 
         if($firstChild = $children->First()) { 
                //DEBUGLOG("Get first child T3");

           $result = $children->First();
         } 
      }


            //DEBUGLOG("Get first child T4");


      return $result;
   }


 
  public function getCMSFields() {
    $fields = parent::getCMSFields();
    $mfuf = new MultipleFileUploadField('AttachedFiles','Upload several images at once');
    $mfuf->image_class =  'ImageFile';
    $mfuf->setUploadFolder('galleries/'.$this->URLSegment);
    $fields->addFieldToTab("Root.Content.BulkUpload", $mfuf);

    $fields->addFieldToTab('Root.Content.BulkUpload', new LiteralField('Bulk Note', 
    '<p>If you wish to set the same details for all of the uploaded images please do so here</p>'));

    $fields->addFieldToTab('Root.Content.BulkUpload', new TextField('BulkTitle', 'Bulk Title'));
    $fields->addFieldToTab('Root.Content.BulkUpload', new TextField('BulkCaption', 'Bulk Caption'));
    $fields->addFieldToTab('Root.Content.BulkUpload', new TextField('BulkCopyright', 'Bulk Copyright'));
    $fields->addFieldToTab('Root.Content.BulkUpload', new TextField('BulkLicense', 'Bulk License'));


 
    $manager = new PhotographDataObjectManager(
      $this, // Controller
      'AllChildren', // Source name
      'Photograph', // Source class
      'Photo', // File name on DataObject
      array(
                'Title' => 'Title', 
                'Caption' => 'Caption'
            ), 
      'getCMSFields_forPopup' // Detail fields (function name or FieldSet object)
      // Filter clause
      // Sort clause
      // Join clause
    );

    $fields->addFieldToTab("Root.Content.AllImages",$manager);

    $l3 = new LiteralField(
      $name = 'literalyfield2',
      $content = '<input id="publishAllPhotosButton_'.$this->ID.'" type="button" class="publishAllPicsButton action" value="Publish All Photos"/>
      '
    );

    $fields->addFieldToTab('Root.Content.AllImages', $l3);

//  Requirements::javascript('silverstripe-translatable-gallery/javascript/fbImport.js');
  Requirements::css('wot-translatable-gallery/css/galleryAdmin.css');



  if (self::CanUseFacebook()) {
  /*   $facebook = new Facebook(array(
    'appId'  => Gallery::getFacebookApplicationID(),
    'secret' => Gallery::getFacebookApplicationSecret(),
    'cookie' => false, // enable optional cookie support
  ));
*/

  $content = '
   <div id="fb-auth" class="action">Login</div>
        <div id="user-info"></div>
 <script type="text/javascript" src="/wot-translatable-gallery/javascript/fbImport.js"></script>
  <p>
    You can import your own and photos  you can see into a gallery.  You will be prompted to authenticate against Facebook prior to importing.
    </p><p>';
    $l1 = new LiteralField(
      $name = 'literallyfield1',
      $content
); 

    $fields->addFieldToTab('Root.Content.Facebook', $l1);


  $fields->addFieldToTab('Root.Content.Facebook', new TextField('FacebookAlbumID', 'Facebook Album ID'));

  $fields->addFieldToTab('Root.Content.Facebook', new TextField('FacebookCoverPicID', 'Facebook Cover Picture ID'));

    $l2 = new LiteralField(
      $name = 'literalyfield2',
      $content = '
      <div id="importButton_'.$this->ID.'" class="facebookImportButton hidden action"><input type="button" class="triggerFacebookImportButton" id="fbImportButton" value="Import"/>
<input type="button" class="facebookLoadAlbumsButton" id="facebookLoadAlbumsButton" value="Load Albums"/>
      </div>
      <div id="facebookGalleryPreview"><p>Images will appear here</p></div>
      '
    );

    $fields->addFieldToTab('Root.Content.Facebook', $l2);
    }
  


    /*
    new ImageDataObjectManager( 
$this, 
'SomeObjects', 
'SomeObject', 
'SomeFile', 
array('Foo' => 'Foo', 'Bar' => 'Bar') 
);
*/
    return $fields;
  }



function deleteProcessedImageFiles() {
   //DEBUGLOG("Deleting images from ImageFile");
    $sql1 = "DELETE from File where ClassName = 'ImageFile' and ID in (select ID from ImageFile where PhotoID != 0 AND GalleryID=".$this->ID.")";
    //DEBUGLOG($sql1);
    DB::query($sql1);
    $sql2 = "DELETE from ImageFile where PhotoID !=0 AND GalleryID=".$this->ID;
    //DEBUGLOG($sql2);
    DB::query($sql2);
}



  function onAfterWrite() {

    parent::onAfterWrite();


   // FormResponse::add("alert('gallery write');");

    if(!self::$has_written) { 
          //FormResponse::add("window.location.reload();");
          //self::$has_written = true; 


            // cehck for image files, as the on after write method is called more than once
          // If we delete too soon, the bulk uploaded ImageFile objects wont get attached to photographs
          $unsortedPhotographs = DataObject::get(
            'Photograph',
            'InitiallySortedByFilename=false AND `SiteTree`.`ParentID`='.$this->ID,
            'Filename',
            'Left Join File on File.ID = PhotoID'
          );

          if ($unsortedPhotographs) {
            $parentID = $this->ID;


          FormResponse::add( <<<JS
            var tree = $('sitetree');
            var parent = tree.getTreeNodeByIdx($parentID);
            var node;
            var nodeList = [];
JS
);

          $i = 1 + DB::query("SELECT Max(Sort) FROM SiteTree WHERE ParentID = ".$this->ID)->value();
       
          foreach ($unsortedPhotographs as $key => $value) {
            error_log("UNSORTED PIC:".$value->ID." ".$value->Title);
            $vid = $value->ID;

            $value->SortOrder = $i;
            $value->Sort = $i;
            $value->InitiallySortedByFilename = true;
            $value->write();
            $i++;

            FormResponse::add( <<< JS
            node = tree.getTreeNodeByIdx($vid);
            parent.removeTreeNode(node);
            nodeList.push(node);
JS
);
          }



          FormResponse::add( <<< JS
      for (var i = 0; i < nodeList.length; i++){ 
  node = nodeList[i];
  //alert(node);
      parent.appendTreeNode(node);

  
}
JS
);
          }

          
          /*
          foreach ($imageFiles as $key => $imageFile) {
            array_push($imageFileIDs, $imageFile->ID);
          }

          $idList = implode(",", $imageFileIDs);

          //DEBUGLOG("ID LIST:".$idList);
      */
        //  //DEBUGLOG("Gallery:  image files ".$imageFiles);
      

        

    }

    $this->deleteProcessedImageFiles();

    }
    




    
    
   
}




class Gallery_Controller extends Page_Controller {

  /**
   * An array of actions that can be accessed via a request. Each array element should be an action name, and the
   * permissions or conditions required to allow the user to access it.
   *
   * <code>
   * array (
   *     'action', // anyone can access this action
   *     'action' => true, // same as above
   *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
   *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
   * );
   * </code>
   *
   * @var array
   */
  public static $allowed_actions = array ('PublishAllPhotos', 'PreviewAlbum', 'ImportPicture', 'ListAlbums');

  public function init() {
    parent::init();
/*
    $this->facebook = new Facebook(array(
      'appId'  => '200187276738808',
      'secret' => '6b5b0e791580c88f4a9ea527d44fdd78',
      'cookie' => false, // enable optional cookie support
    ));
*/
    error_log("GALLERY INIT REQUEST");
    error_log(print_r($_REQUEST,1));
  }

    public function ColumnLayout() {
      return 'layout1col';
    }


    /* Publish all the photographs in a gallery */
    function PublishAllPhotos($request) {
      error_log(print_r($request,1));
      $unesc_id = Director::urlParam('ID');
      $id = Convert::raw2sql($unesc_id);
      error_log("ID of gallery:*".$id."*");
      error_log("Publising all photos");
      Versioned::reading_stage('Stage');
      $gallery = DataObject::get_by_id('Gallery', $id);
      error_log("GALLERY:".$gallery);

      $publishedIDS = array();

      // change locale to that of the gallery
      i18n::set_locale($gallery->Locale);
      Translatable::set_current_locale($gallery->Locale);

       
      foreach($gallery->Children() as $photo){
        error_log("CHILD:".$photo);
        error_log("Publishing ".$photo);
        $photo->Publish('Stage', 'Live');
        //$this->tellBrowserAboutPublishedPhoto($photo);
        $JS_title = Convert::raw2js($photo->TreeTitle());

        $JS_stageURL = $photo->IsDeletedFromStage ? '' : Convert::raw2js($photo->AbsoluteLink());
        $liveRecord = Versioned::get_one_by_stage('SiteTree', 'Live', "\"SiteTree\".\"ID\" = $photo->ID");

        $JS_liveURL = $liveRecord ? Convert::raw2js($liveRecord->AbsoluteLink()) : '';

        $image = array();
        $image['id'] = $photo->ID;
        $image['title'] = $JS_title;
        $image['stageURL'] = $JS_stageURL;
        $image['liveURL'] = $JS_liveURL;
        $image['Status'] = $photo->Status;


        array_push($publishedIDS, $image);
      }

      Versioned::reading_stage('Live');


      echo json_encode($publishedIDS);  
      die;        
    } 
    
    
    
    function tellBrowserAboutPublishedPhoto($page) {
      $JS_title = Convert::raw2js($page->TreeTitle());

      $JS_stageURL = $page->IsDeletedFromStage ? '' : Convert::raw2js($page->AbsoluteLink());
      $liveRecord = Versioned::get_one_by_stage('SiteTree', 'Live', "\"SiteTree\".\"ID\" = $page->ID");

      $JS_liveURL = $liveRecord ? Convert::raw2js($liveRecord->AbsoluteLink()) : '';

      //FIXMEFormResponse::add($this->getActionUpdateJS($page));
      FormResponse::update_status($page->Status);
      
      if($JS_stageURL || $JS_liveURL) {
        FormResponse::add("\$('sitetree').setNodeTitle($page->ID, '$JS_title');");
      } else {
        FormResponse::add("var node = $('sitetree').getTreeNodeByIdx('$page->ID');");
        FormResponse::add("if(node && node.parentTreeNode) node.parentTreeNode.removeTreeNode(node);");
        FormResponse::add("$('Form_EditForm').reloadIfSetTo($page->ID);");
      }
      
      FormResponse::add("$('Form_EditForm').elements.StageURLSegment.value = '$JS_stageURL';");
      FormResponse::add("$('Form_EditForm').elements.LiveURLSegment.value = '$JS_liveURL';");
      FormResponse::add("$('Form_EditForm').notify('PagePublished', $('Form_EditForm').elements.ID.value);");

      // dont respond just yet
    }  


    //FIXME - add permissions checks
    public function ListAlbums($request) {
      $user = $this->facebook->getUser();
      error_log("LIST ALBUMS: USER - ".$user);
      //FIXME - if this is blank then appropriate error
        $fql    =   "SELECT aid, cover_pid, name, description FROM album where owner=".Gallery::getFacebookUserID(); //  WHERE aid='1069035736_130940'";
          $param  =   array(
           'method'    => 'fql.query',
           'query'     => $fql,
           'callback'  => ''
          );

          error_log($fql);
          $fqlResult   =   $this->facebook->api($param);

          error_log("FQL RESULT GOT");

          $result = array();
          $result['fql']=$fqlResult;
          error_log(print_r($fqlResult,1));
          echo json_encode($result);
          die;
    }



    public function PreviewAlbum($request) {
      // FIXME - check for permissions to create gallery
        error_log("Video metadata request");
        error_log(print_r($request,1));
        $albumID = Convert::raw2sql($request['AlbumIDOrURL']);

        $fql    =   "SELECT pid, src, src_small, src_big, caption FROM photo WHERE aid = '" . $albumID ."'  ORDER BY created DESC";// limit 4";
        error_log($fql);
        $param  =   array(
         'method'    => 'fql.query',
         'query'     => $fql,
         'callback'  => ''
        );
        $fqlResult   =   $this->facebook->api($param);

        $result['fql']=$fqlResult;

        $images = array();

        foreach( $fqlResult as $keys => $values ){
    
          if( $values['caption'] == '' ){
            $caption = "";
          }else{
            $caption = $values['caption'];
          }

          $image = array();


          $image['caption'] = $caption;
          $image['pid'] = $values['pid'];
          $image['src_small'] = $values['src_small'];
          $image['src_big'] = $values['src_big'];
          

          array_push($images, $image);
        }

        $result['images'] = $images;

        /*

"0":{
"pid":"4591473524378070457",
"src":"https:\/\/fbcdn-photos-a.akamaihd.net\/hphotos-ak-ash4\/394577_2642141487319_1069035736_2780601_1675166622_s.jpg",
"src_small":"https:\/\/fbcdn-photos-a.akamaihd.net\/hphotos-ak-ash4\/394577_2642141487319_1069035736_2780601_1675166622_t.jpg",
"src_big":"https:\/\/fbcdn-sphotos-a.akamaihd.net\/hphotos-ak-ash4\/s720x720\/394577_2642141487319_1069035736_2780601_1675166622_n.jpg",
"caption":""}
        */
       

        $result['thumbnail1'] = 'this is a test';
   
        $this->response->setStatusCode(200, "Found " );

        echo json_encode($result);

        
        die;
    }


    /* Import a single picture */
    function ImportPicture($request) {
    error_log(print_r($request,1));

      /*
      $p = new Page();
      $p->Title = 'This is a test';
      $p->Content = 'Content body test';
      $p->ParentID = 238;
      $p->Locale = Translatable::get_current_locale();


      error_log("P:validation_enabled:".$p->get_validation_enabled());
      //error_log("VALIDATION:".$p->validate());
      $p->write(false,true);
      error_log("PAGE:".print_r($p,1));
      error_log("PAGE ID AFTER WRITE:".$p->ID);
      die;
*/
      error_log("++++++++++++++ IMPORT PIC T1");
        //error_log(print_r($request,1));
error_log("T2");
        $gid = Convert::raw2sql($request->param('ID'));
        $isCover = Convert::raw2sql($_POST['cover']);
error_log("T3 - gid = ".$gid);
error_log("COVER:".$isCover);

  

        // we want to deal with staging only
        Versioned::reading_stage('Stage');
        $gallery = DataObject::get_by_id('Gallery', $gid);
        Versioned::reading_stage('Live');

        error_log("Gallery:".$gallery->ID);
        error_log("Gallery locale:".$gallery->Locale);

        error_log("T3a");
        //$albumID = Convert::raw2sql($request['AlbumIDOrURL']);
        $result = array();
        //FIXME - make assets programmable, not hardwired
        $uploadFolderPath = "/galleries/".$gallery->URLSegment;
        $uploadFolder = Folder::findOrMake($uploadFolderPath);


error_log("T4");
        $absPath = Director::baseFolder().'/assets'.$uploadFolderPath;
        error_log("ABS PATH:".$absPath);
        error_log("UPLOAD FOLDER");
        error_log(print_r($uploadFolder,1));
        error_log("T5");

        $pid = Convert::raw2sql($request['pid']);
        $caption = Convert::raw2sql($request['caption']);
        $src_big = Convert::raw2sql($request['src_big']);


        $filepath = $absPath.'/'.$pid.'.JPG';

        error_log("WGET FILE PATH:".$filepath);
        

         $cmd = "wget --no-check-certificate -O $filepath $src_big";

         error_log("COMMAND:".$cmd);
         exec($cmd);

         error_log("T11 - creating iamge");
        $image = new Image();
        $image->Name = $pid;
        $image->Title = $pid;
        error_log("UPLOAD FOLDER PATH:".$uploadFolderPath);
        $imagepath = str_replace('/assets/', '', $uploadFolderPath);
        $imagepath .= "/$pid.JPG";
        error_log("IMAGE PATH:".$imagepath);


        $image->Filename = "$pid.JPG";
        $image->ParentID = $uploadFolder->ID;
        $image->write();

      /*  // add a cover pic if we do not have one
        if ($isCover == 'true') {
          error_log("Setting main cover image");
          error_log("THIS:".$this);
          if (!$gallery->PromotionImageID) {

            $ci = new CustomImage();
            $ci->Filename = "$pid.JPG";
            $ci->ParentID = $uploadFolder->ID;
            $ci->write();
            error_log("Promotion id being set to ".$ci->ID);
          //  $gallery->PromotionImage = $ci;
          //  $gallery->write();
            $sql = "update Page set PromotionImageID = ".$ci->ID." where ID=".$gallery->ID;
           error_log($sql);
DB::query($sql);
          } else {
            error_log("Promotion image previously set");
          }
        }

        */


error_log("T11 - creating photograph");
        Versioned::reading_stage('Stage');

        $pic = new Photograph();

        if (!$caption) {
          $caption = '';
          $pic->Title = $pid;
          $pic->Caption = $caption;

        } else {
          // limit tite to 6 words
          $words = explode( ' ', $caption ); 
          $title = implode( ' ', array_slice( $words, 0, 6 ) );
          $pic->Title = $title;
          $pic->Caption = $caption;
        }

        $pic->PhotoID = $image->ID;
        $pic->ParentID = $gid;
        $pic->Locale = $gallery->Locale; //Translatable::get_current_locale();


        error_log("Photograph: PhotoID:".$image->ID);
        error_log("Photo parent id:".$pic->ParentID);
        error_log("Photograph LOCALE".$pic->Locale);

        $pic->write();

        // prime the Javascript in order to write to the tree
        $parentID = (int) $pic->ParentID;
        $id = $pic->ID ? $pic->ID : "new-$pic->class-$pic->ParentID";
        $treeTitle = Convert::raw2js($pic->TreeTitle());
        $clazz = $pic->class;
        $hasChildren = (is_numeric($id) && $pic->AllChildren() && $pic->AllChildren()->Count()) ? ' unexpanded' : '';

        $result['parentID'] = $parentID;
        $result['id'] = $id;
        $result['treeTitle'] = $treeTitle;
        $result['hasChildren'] = $hasChildren;
        $result['class'] = $clazz;



        //error_log("WRITE:".$x." ID OF PHOTOGRAPH IN DB:".$pic->ID);

       // $pic->Publish('Live', 'Stage');
       // $pic->doUnpublish();

        Versioned::reading_stage('Live');




        /*

        $new_name = trim($request->requestVar('NewFolder'),"/");
                                        $clean_path = self::relative_asset_dir($upload_folder->Filename);
                                        $new_folder = Folder::findOrMake($clean_path.$new_name);
*/

        $result['success'] = true;

                //Versioned::reading_stage('Live');


                echo json_encode($result);


        die;
    }

}

?>