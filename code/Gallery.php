<?php
class Gallery extends Page {
 
  static $has_many = array (
    'AttachedFiles' => 'ImageFile'
  );
 
  public function getCMSFields() {
    $fields = parent::getCMSFields();
    $fields->addFieldToTab("Root.Content.BulkUpload", new MultipleFileUploadField('AttachedFiles','Upload several files'));
    return $fields;
  }


  function onBeforeWrite() {
    parent::onBeforeWrite();
    error_log("Gallery on before write");
  }


  function onAfterWrite() {
    parent::onAfterWrite();

    error_log("Gallery on after write");


    // cehck for image files, as the on after write method is called more than once
    // If we delete too soon, the bulk uploaded ImageFile objects wont get attached to photographs
    $imageFiles = DataObject::get('ImageFile', 'GalleryID='.$this->ID);
    
    if ($imageFiles) {
      // we cannot use $imageFile->delete as this deletes the record in the File table
      // instead delete the ImageFile records using raw sql
      DB::query("DELETE from ImageFile where GalleryID=".$this->ID);

    }
    error_log("deleted image files");
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

    error_log("Gallery init");

    // Note: you should use SS template require tags inside your templates 
    // instead of putting Requirements calls here.  However these are 
    // included so that our older themes still work
    Requirements::themedCSS('gallery.css');
  }
}

?>