<?php
/**
 * Defines the Link page type
 */
class Photograph extends Page {
   static $db = array(
    'Caption' => 'Text',
    'Copyright' => 'Text',
    'License' => 'Text',
    'Photographer' => 'Text',
    'ShowOnHomePage' => 'Boolean'
  );
  
    static $has_one = array(
      //reference name => data object type
      // this is saved as PhotoID in the Photograph table
      'Photo' => 'Image',
      'Contributor' => 'Member'
      );
      

      static $belongs_to = array(
        'Gallery'
        );
      
      

      
        
  
 public function getCMSFields_forPopup()
  {
    return new FieldSet(
      new TextField('Caption')
    );
  }

  function getCMSFields() {
    $fields = parent::getCMSFields();
    //$fields->addFieldToTab("Root.Content.Main", new CheckboxField('ShowOnHomePage'));
    //$fields->renameField("ShowOnHomePage", "Display Image on Home Page?");

    $fields->addFieldToTab("Root.Content.Main", new TextField('Caption'));
    $fields->addFieldToTab("Root.Content.ImageInfo", new TextField('Photographer'));
    $fields->addFieldToTab("Root.Content.ImageInfo", new TextField('Copyright'));
    $fields->addFieldToTab("Root.Content.ImageInfo", new TextField('License'));

    $fields->removeFieldFromTab("Root.Content.Main","Content");
    //$fields->addFieldToTab("Root.Content.Image", new ImageUploadField('Photo'));
    $fields->addFieldToTab("Root.Content.Main", new ImageField('Photo'));


    //$fields->renameField("Content", "Description");
     return $fields;
  }
  
  
  function IsFirst() {
    return ($this->iteratorPos) == 0;
  }


}
 
class Photograph_Controller extends Page_Controller {
 
}
 
?>