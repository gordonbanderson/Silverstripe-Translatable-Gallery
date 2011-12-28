<?php
class GalleryCMSActionDecorator extends LeftAndMainDecorator {
     
    function doPublishAllPhotos(){
    	$id = (int)$_REQUEST['ID']; 
    	error_log("Publising all photos");
     
	    $gallery = DataObject::get_by_id('Gallery', $id);
	     
	    foreach($gallery->Children() as $photo){
	    	error_log("Publishing ".$photo);
	    	$photo->Publish('Stage', 'Live');
	    	$this->tellBrowserAboutPublishedPhoto($photo);
	    }
     
        FormResponse::status_message(sprintf('All good!'),'good');
        return FormResponse::respond();
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
}

/*
return $this->tellBrowserAboutPublicationChange(
					$publishedRecord, 
					sprintf(
						_t(
							'LeftAndMain.STATUSPUBLISHEDSUCCESS', 
							"Published '%s' successfully",
							PR_MEDIUM,
							'Status message after publishing a page, showing the page title'
						),
						$record->Title
					)
				);
				*/

?>