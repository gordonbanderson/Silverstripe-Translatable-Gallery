<?php
/**
 * Defines the GalleryFolder page type
 */
class GalleryFolder extends Page {

   
   static $allowed_children = array('Gallery', 'GalleryFolder');

   static $has_one = array(
      'CoverPhoto' => 'Image',
   );


   function getCMSFields() {
    $fields = parent::getCMSFields();
    $fields->addFieldToTab("Root.Content.CoverPhoto", new ImageField('CoverPhoto'));

    
    $fields->renameField("Content", "Brief Description");
   

    /*
    $fields->addFieldToTab('Root.Content.Main', new CalendarDateField('Date'), 'Content');
    $fields->addFieldToTab('Root.Content.Main', new TextField('Author'), 'Content');
    */
    return $fields;
  }
  
}
 
class GalleryFolder_Controller extends Page_Controller {
   public function ColumnLayout() {
      return 'layout1col';
    }
}
 
?>