<?php

class PhotographDataObjectManager extends FileDataObjectManager
{
	protected static $sliderWidth = 150;
	protected static $minImageSize = 25;
	protected static $maxImageSize = 300;


	public $view = "grid";
	protected $limitFileTypes = array ('jpg','jpeg','gif','png');
	public $template = "PhotographDataObjectManager";
	public $itemClass = "PhotographDataObjectManager_Item";
	public $popupClass = "PhotographDataObjectManager_Popup";
	public $importClass = "Image";
	
	public $imageSize = 100;
	
	public $uploadifyField = "MultipleImageUploadField";

	public function __construct($controller, $name = null, $sourceClass = null, $fileFieldName = null, $fieldList = null, $detailFormFields = null, $sourceFilter = "", $sourceSort = "", $sourceJoin = "") 
	{
		parent::__construct($controller, $name, $sourceClass, $fileFieldName, $fieldList, $detailFormFields, $sourceFilter, $sourceSort, $sourceJoin); 
		
		//DEBUGLOG("CONSTRUCTOR PDOM");
		Requirements::css('dataobject_manager/css/ui/dom_jquery_ui.css');


		if(isset($_REQUEST['ctf'][$this->Name()])) {		
				$this->imageSize = $_REQUEST['ctf'][$this->Name()]['imagesize'];
		}
		$this->setAllowedFileTypes($this->limitFileTypes);
		$this->GalleryID=$controller->ID;
		//DEBUGLOG("GALLERY ID IN PDOM:".$this->GalleryID);
	}


	function Photographs() {

		$items = $this->Items();

		//DEBUGLOG("ITEMS CLASS:".$items);

		$photoArray =  array();

		foreach ($items as $key => $item) {

			//DEBUGLOG("ITEM: ".$item);
			//DEBUGLOG($item->ID);

			$photoArray[$item->ID] = $photoArray;
			# code...
		}
		//var_dump($items);
		//$mySet->sort('Lastname'); 
		$gallery = DataObject::get('Gallery', $this->GalleryID);
		//DEBUGLOG("DATA GALLERY:".$gallery);

		/*
		$photoPages = $gallery->AllChildren();
		foreach ($photoPages as $key => $photoPage) {
			$photoID = $photoPage->PhotoID;
			$sortOrder = $photoPage->SortOrder;
			//DEBUGLOG("PHOTO ID:".$photoID." -> SORT: ".$sortOrder);
		}

		$items->sort('SortOrder');
		*/
		return $items;
	}


	function handleItem($request) {
		return new PhotographDataObjectManager_ItemRequest($this, $request->param('ID'));
	}

	public function getQueryString($params = array())
	{ 
		$imagesize = isset($params['imagesize'])? $params['imagesize'] : $this->imageSize;
		return parent::getQueryString($params)."&ctf[{$this->Name()}][imagesize]={$imagesize}";
	}
	
	public function SliderPercentage()
	{
		return ($this->imageSize - self::$minImageSize) / ((self::$maxImageSize - self::$minImageSize) / 100);
	}
	
	public function SliderPosition()
	{
		return floor(($this->SliderPercentage()/100) * self::$sliderWidth); // handle is 16px wide
	}
		

}

class PhotographDataObjectManager_Item extends FileDataObjectManager_Item 
{

	function __construct(DataObject $item, ComplexTableField $parent)
	{
		parent::__construct($item, $parent);
	}

	public function FileIcon()
	{
		$file = ($this->parent->hasDataObject) ? $this->obj($this->parent->fileFieldName) : $this->item;
		if($file) {
			if($this->parent->imageSize <= 50) $size = 50;
			elseif($this->parent->imageSize <= 100) $size = 100;
			elseif($this->parent->imageSize <= 200) $size = 200;
			else $size = 300;
			return ($file instanceof Image && $cropped = $file->CroppedImage($size, $size)) ? $cropped->URL : $file->Icon();
		}
		return false;
	}
	
	public function ImageSize()
	{
		return $this->parent->imageSize;
	}


	

}


class PhotographDataObject_Controller extends FileDataObjectManager_Controller {
	function Photographs() {
		return DataObject.get('Photograph');
	}


	public function dosort()
	 {
	 	//DEBUGLOG("**** PHOTO DOM CONTROLLER - do sort");
	 	//DEBUGLOG(print_r($_POST,1));
	    if(!empty($_POST) && is_array($_POST)) {
	    	//DEBUGLOG("T1");
	      $className = 'Photograph'; // set by routing
	      if(stristr($className,"-") !== false) {
	      	//DEBUGLOG("T2");
	       list($ownerClass, $className) = explode("-",$className);
	      }
	      $many_many = ((is_numeric($this->urlParams['OtherID'])) && SortableDataObject::is_sortable_many_many($className));
	      foreach($_POST as $group => $map) {
	      	//DEBUGLOG("T3");
	        if(substr($group, 0, 7) == "record-") {
	          if($many_many) {
	            $controllerID = $this->urlParams['OtherID'];          
	            $candidates = singleton($ownerClass)->many_many();
	            if(is_array($candidates)) {
	              foreach($candidates as $name => $class)
	                if($class == $className) {
	                  $relationName = $name;
	                  break;
	                }
	            }
	            if(!isset($relationName)) return false;
	            list($parentClass, $componentClass, $parentField, $componentField, $table) = singleton($ownerClass)->many_many($relationName);  
	            
	            //DEBUGLOG("B1");          
	            foreach($map as $sort => $id) {
	              DB::query("UPDATE \"$table\" SET \"SortOrder\" = $sort WHERE \"{$className}ID\" = $id AND \"{$ownerClass}ID\" = $controllerID");
	              DB::query("UPDATE \"$table\" SET \"Sort\" = $sort WHERE \"{$className}ID\" = $id AND \"{$ownerClass}ID\" = $controllerID");
	          	}
	          }
	          else {
	          	//DEBUGLOG("B2");



	          	$sortOrder = array();
	            foreach($map as $sort => $id) {
	            	//DEBUGLOG("SORTING:$id -> $sort");
	              $obj = DataObject::get_by_id($className, $id);
	              $obj->SortOrder = $sort;
	              $obj->Sort = $sort;
	              $obj->write();

	              //FIXME - should do this more efficiently
	              //propogate the sort to published items if they are published
	              if ($obj->isPublished()) {
	              	$obj->Publish('Stage', 'Live');
	              }


	              array_push($sortOrder, $id);
	            }

	            //DEBUGLOG("SORT ORDER");
	            //DEBUGLOG(print_r($sortOrder,1));

	            if ($sortOrder) {
	            	$cid = $sortOrder[0];
	            	//DEBUGLOG("DESIRED CHILD ID:".$cid);
	            	$child = DataObject::get_by_id('Photograph',$cid);
	            	//DEBUGLOG("CHILD ID:".$child->ID);
	            	$parentID = (int) $child->ParentID;
	            	$response = '';
	            	FormResponse::add( <<<JS
var tree = $('sitetree');
var parent = tree.getTreeNodeByIdx($parentID);
var node;
var nodeList = [];
JS
					);

					foreach ($sortOrder as $key => $value) {
						FormResponse::add( <<< JS
node = tree.getTreeNodeByIdx($value);
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


	          }
	          break;
	        }
	      }
	    }

	    if(Director::is_ajax()) {
	    	//DEBUGLOG("DIR AJAX");
	    	//FormResponse::add("alert('PDOM response from code');");
        	FormResponse::status_message('Sorted', 'good');
			return FormResponse::respond();
	    } else {
	    	//DEBUGLOG("DIR NOT AJAX");
	    }
	}
}

class PhotographDataObjectManager_Popup extends FileDataObjectManager_Popup
{
	function __construct($controller, $name, $fields, $validator, $readonly, $dataObject) 
	{
			parent::__construct($controller, $name, $fields, $validator, $readonly, $dataObject);
			Requirements::css('dataobject_manager/css/imagedataobject_manager.css');
			Requirements::javascript('silverstripe-links/javascript/photograph_popup.js');
	}



}



class PhotographDataObjectManager_ItemRequest extends DataObjectManager_ItemRequest 
{
	function __construct($ctf, $itemID) 
	{
		parent::__construct($ctf, $itemID);
	}
	
	function DetailForm($childID = null)
	{	
		if($this->ctf->hasDataObject) {
			$fileField = $this->ctf->fileFieldName;
			$imgObj = $this->dataObj()->$fileField();
		}
		else
			$imgObj = $this->dataObj();
		$form = parent::DetailForm($childID);
		$form->Fields()->insertAfter($this->ctf->getPreviewFieldFor($imgObj, 200), 'open');
		return $form;
	}


	function saveComplexTableField($data, $form, $request) {
		$dataObject = $this->dataObj();

		//DEBUGLOG("SACVING COMPLEXT TABLE DATA, wooot");
				//DEBUGLOG("AJAX?:".Director::is_ajax());


		try {
						//DEBUGLOG("T1");

			$form->saveInto($dataObject);
			$dataObject->write();
		} catch(ValidationException $e) {
			$form->sessionMessage($e->getResult()->message(), 'bad');
			//DEBUGLOG("T2");
			return Director::redirectBack();
		}
		
		// Save the many many relationship if it's available
		if(isset($data['ctf']['manyManyRelation'])) {
						//DEBUGLOG("T3");

			$parentRecord = DataObject::get_by_id($data['ctf']['parentClass'], (int) $data['ctf']['sourceID']);
			$relationName = $data['ctf']['manyManyRelation'];
			$componentSet = $parentRecord->getManyManyComponents($relationName);
			$componentSet->add($dataObject);
		}
		
		$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
		
		$closeLink = sprintf(
			'<small><a href="%s" onclick="javascript:window.top.GB_hide(); return false;">(%s)</a></small>',
			$referrer,
			_t('ComplexTableField.CLOSEPOPUP', 'Close Popup')
		);
		$message = sprintf(
			_t('ComplexTableField.SUCCESSEDIT', 'Saved %s %s %s'),
			$dataObject->singular_name(),
			'<a href="' . $this->Link() . '">"' . htmlspecialchars($dataObject->Title, ENT_QUOTES) . '"</a>',
			$closeLink
		);

					//DEBUGLOG("T4");

		
		$form->sessionMessage($message, 'good');

//DEBUGLOG("T5");
		////DEBUGLOG("IS AJAX:".Director::isAjax());
		 FormResponse::add("alert('from popup');");
		//DEBUGLOG("T6");
      	return FormResponse::respond();
		//Director::redirectBack();
	}
	

}

?>