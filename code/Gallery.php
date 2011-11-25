<?php
class Gallery extends Page {
  static $db = array (
    'BulkTitle' => 'Varchar',
    'BulkCaption' => 'Varchar',
    'BulkCopyright' => 'Varchar',
    'BulkLicense' => 'Varchar'
  );

 
  static $has_many = array (
    'AttachedFiles' => 'ImageFile'
  );

  //Uncle Cheese hack from http://silverstripe.org/data-model-questions/show/6805 << to detect multiple calls to on after write or on before write
  static $has_written = false;



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
          self::$has_written = true; 


            // cehck for image files, as the on after write method is called more than once
          // If we delete too soon, the bulk uploaded ImageFile objects wont get attached to photographs
          $imageFiles = DataObject::get('ImageFile', 'GalleryID='.$this->ID, 'Filename');

          $imageFileIDs = array();

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
    
    //this only does the main panel - LeftAndMain::ForceReload();



    
   
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
  public static $allowed_actions = array ('Photograph');

  public function init() {
    parent::init();

    // Note: you should use SS template require tags inside your templates 
    // instead of putting Requirements calls here.  However these are 
    // included so that our older themes still work
    Requirements::themedCSS('gallery.css');
  }

    public function ColumnLayout() {
      return 'layout1col';
    }
}

?>