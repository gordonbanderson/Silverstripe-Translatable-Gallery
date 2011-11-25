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
      new TextField('Title'),
      new TextField('Caption')
    );

    $this->FromPopup = true;
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



  public function returnItemToUser($p) {
    error_log("returning JS to user from photo");
    if(Director::is_ajax()) {
      // Prepare the object for insertion.
      $parentID = (int) $p->ParentID;
      $id = $p->ID ? $p->ID : "new-$p->class-$p->ParentID";
      $treeTitle = Convert::raw2js($p->TreeTitle());
      $hasChildren = (is_numeric($id) && $p->AllChildren() && $p->AllChildren()->Count()) ? ' unexpanded' : '';

      // Ensure there is definitly a node avaliable. if not, append to the home tree.
      $response = <<<JS
        alert('photo from saving');
        var tree = $('sitetree');
        var newNode = tree.createTreeNode("$id", "$treeTitle", "{$p->class}{$hasChildren}");
        node = tree.getTreeNodeByIdx($parentID);
        if(!node) {
          node = tree.getTreeNodeByIdx(0);
        }
        node.open();
        node.appendTreeNode(newNode);
        //newNode.selectTreeNode();
JS;
      FormResponse::add($response);

      return FormResponse::respond();
    } else {
      Director::redirect('admin/' . self::$url_segment . '/show/' . $p->ID);
    }
  }


  function getRequirementsForPopup() {
    Requirements::javascript('silverstripe-links/javascript/photograph_popup.js');
  }



  public function onBeforeWrite() {

    parent::onBeforeWrite();

    // give data object photo sorter priority here
  
    if ($this->Owner->Sort != $this->Owner->SortOrder) {
       $this->Owner->Sort = $this->Owner->SortOrder;
    }


    if (!isset($_POST['ToDo'])) {
      // we came from the popup
      error_log("FROM POPUP");
           // FormResponse::add("alert('from popup');");
      // FormResponse::respond();

    }

  //  error_log("OBJ: ".$this->ID." SORT=".$this->Sort.", SORT ORDER=".$this->SortOrder);  
  //  error_log("OBJ OWNER: ".$this->ID." SORT=".$this->owner->Sort.", SORT ORDER=".$this->owner->SortOrder);

  error_log("POPUP? ".$this->FromPopup);
    
    // If the sort order and sort params are diffrent, assumed changed by photo dom
    

       error_log("Tweaking tree for save");

      $parentID = (int) $this->ParentID;
      $id = $this->ID ? $this->ID : "new-$this->class-$this->ParentID";
      $treeTitle = Convert::raw2js($this->TreeTitle());
      $hasChildren = (is_numeric($id) && $this->AllChildren() && $this->AllChildren()->Count()) ? ' unexpanded' : '';



      //static DataObject get_one( string $callerClass, [string $filter = ""], [boolean $cache = true], [string $orderby = ""])

      $where = 'ParentID = '.$parentID;// ' and ShortOrder > '.$this->SortOrder;
      error_log("WHERE:".$where);
      $nextItem = DataObject::get_one(
        'Photograph',
        $where,
        false,
        'SortOrder Asc'
      );

      error_log("NEXT ITEM");
      error_log($nextItem);

      $nextID = '';
      $hasNext = 'false';

      error_log("CURRENT ID:".$this->owner->ID."(".$this->owner->SortOrder.") NEXT IS ".$nextID);

      if ($nextItem) {
        $nextID = $nextItem->ID;
        if ($nextID) {
          $hasNext = 'true';
        }
      }

/*
       FormResponse::add( <<<JS
        var tree = $('sitetree');

        var parent = tree.getTreeNodeByIdx($parentID);
        var node = tree.getTreeNodeByIdx($id);



         parent.removeTreeNode(node);

         if ($hasNext) {
            var beforeNode = tree.getTreeNodeByIdx($nextID);
            parent.appendTreeNode(node, beforeNode);

         } else {
            parent.appendTreeNode(node);
         }

         //alert(node);

     
       //}
       
JS
);

      // $this->Sort = $this->Owner->SortOrder;
    //$this->returnItemToUser($this);

  }  
*/
      
   //  } 
  }


}
 
class Photograph_Controller extends Page_Controller {
 
}
 
?>