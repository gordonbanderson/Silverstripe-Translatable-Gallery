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
      error_log("Get first child T1");
      if($children = $this->Children()) {
            error_log("Get first child T2");
 
         if($firstChild = $children->First()) { 
                error_log("Get first child T3");

           $result = $children->First();
         } 
      }


            error_log("Get first child T4");


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

    error_log("ID LIST:".$idList);
*/
    error_log("Gallery: Deleting image files ".$imageFiles);
    
    if ($imageFiles) {
      error_log("IMAGE FILES FOUND");
      // we cannot use $imageFile->delete as this deletes the record in the File table
      // instead delete the ImageFile records using raw sql

      // find the existing max sort order
  
      foreach ($imageFiles as $key => $value) {
            error_log("IMAGEFILE:".$value->ID);
            error_log("    TITLE:".$value->Title);
            error_log("    FILENAME:".$value->Filename);
      }    


     
      // it is time to redraw the photographs in the correct order

      // 1) normalise sort values and remove visually from the tree
      $so = 1;
   //   $photographs = DataObject::get('Photograph',
  //      'ParentID='.$this->ID.' and PhotoID in ('.$idList.')'
  //    );

      $photographs = DataObject::get(
        'Photograph',
        '`SiteTree`.`ParentID`='.$this->ID,

        'Filename',
        'Left Join File on PhotoID = File.ID'
      );

      error_log("Photographs found:".$photographs.count('ID'));


      //FIXME, check logic
      $parentID = $this->ID;

                $response = '';
                FormResponse::add( <<<JS
var tree = $('sitetree');
var parent = tree.getTreeNodeByIdx($parentID);
var node;
var nodeList = [];
JS
);

      foreach($photographs as $key => $value) {
        error_log("**** Normalizing (".$Value->ID.") ".$value->Title);
        $value->SortOrder = $so;
        $value->Sort = $so;
        $value->write();
        $vid = $value->ID
        $so = $so + 1;

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


error_log("Deleting images from ImageFile");
 DB::query("DELETE from File where ClassName = 'ImageFile' and ID in (select ID from ImageFile where GalleryID=".$this->ID.")");
      DB::query("DELETE from ImageFile where GalleryID=".$this->ID);

      

    }
    }
    
    //this only does the main panel - LeftAndMain::ForceReload();



    
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