<?php
/**
 * Defines the Link page type
 */
class Photograph extends Page {
   static $db = array(
	  'Caption' => 'Text',
	  'Source' => 'Text',
	  'License' => 'Text',
    'ShowOnHomePage' => 'Boolean'
  );
  
    static $has_one = array(
      //reference name => data object type
      // this is saved as PhotoID in the Photograph table
      'Photo' => 'Image'
      //,
      //'MakeOfCamera' => 'Camera'
      );
      
      static $belongs_to = array(
        'Gallery'
        );
      
      

      
        
  
 

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Content.Main", new CheckboxField('ShowOnHomePage'));
    $fields->renameField("ShowOnHomePage", "Display Image on Home Page?");

		$fields->addFieldToTab("Root.Content.Main", new TextField('Caption'));
//		$fields->addFieldToTab("Root.Content.ImageInfo", new TextField('Source'));
//		$fields->addFieldToTab("Root.Content.ImageInfo", new TextField('License'));

	  $fields->removeFieldFromTab("Root.Content.Main","Content");
    $fields->addFieldToTab("Root.Content.Image", new ImageUploadField('Photo'));
   // $fields->addFieldToTab("Root.Content.Image", new ImageField('Photo'));


    
    
    
        
 /*       
    $tablefield = new HasOneComplexTableField(
         $this,
         'MakeOfCamera',
         'Camera',
         array(
	        'Manufacturer' => 'Manufacturer',
          'Model' => 'Model'
         ),
         'getCMSFields_forPopup'
      );
      $tablefield->setParentClass('Camera');
	  $fields->addFieldToTab( 'Root.Content.PhotoMetadata', $tablefield);
*/

		//$fields->renameField("Content", "Description");
	   return $fields;
	}
  
  
  function IsFirst() {
 		error_log("IS FIRST ITERATOR POS:" . $this->iteratorPos);
 		return ($this->iteratorPos) == 0;
 	}


}
 
class Photograph_Controller extends Page_Controller {
 
}
 
?>
