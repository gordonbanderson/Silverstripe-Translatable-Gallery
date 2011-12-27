<?
class ImageFile extends Image {
 
	static $has_one = array (
		'Gallery' => 'Gallery'
	);


	static $db = array(
    'PhotoID' => 'Int'
  	);


	public function returnItemToUser($p) {
		if(Director::is_ajax()) {
			// Prepare the object for insertion.
			$parentID = (int) $p->ParentID;
			$id = $p->ID ? $p->ID : "new-$p->class-$p->ParentID";
			$treeTitle = Convert::raw2js($p->TreeTitle());
			$hasChildren = (is_numeric($id) && $p->AllChildren() && $p->AllChildren()->Count()) ? ' unexpanded' : '';

			// Ensure there is definitly a node avaliable. if not, append to the home tree.
			$response = <<<JS
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


	


	
	public function onBeforeWrite() {
		parent::onBeforeWrite();

		//DEBUGLOG("IF: Image file on before write");

		// create a photograph using these details
		if ($this->GalleryID) {
			//FIXME - still need to sort by filename
			
			//DEBUGLOG("IF: T2");

			//DEBUGLOG("IMAGE FILE:(".$this->SortOrder.') '.$this->Title);


			$photo = new Photograph();
			

			// the gallery has not yet been saved, so use the POST parameters after sanitising them
			$bulkTitleRaw = $_POST['BulkTitle'];
			$bulkCaptionRaw = $_POST['BulkCaption'];
			$bulkCopyrightRaw = $_POST['BulkCopyright'];
			$bulkLicenseRaw = $_POST['BulkLicense'];

			//default to the photo title if no bulk title provided.  Items with no title get annoying to edit...
			if(!$bulkTitleRaw) {
				$photo->Title = $this->Title;
	
			} else {
				$bulkTitle = Convert::raw2sql($bulkTitleRaw);
				$photo->Title = $bulkTitle;
			};

			// set the bulk caption if one available
			if ($bulkCaptionRaw) {
				$photo->Caption = Convert::raw2sql($bulkCaptionRaw);
			}

			// set the bulk license if one available
			if ($bulkLicenseRaw) {
				$photo->License = Convert::raw2sql($bulkLicenseRaw);
			}

			// set a bulk caption if one available
			if ($bulkCopyrightRaw) {
				$photo->Copyright = Convert::raw2sql($bulkCopyrightRaw);
			}


			//Both the parent ID and the correct locale must be set, otherwise things get very messed up
			$photo->ParentID = $this->GalleryID;

			//set the locale
			$photo->Locale = Translatable::get_current_locale();

			$image = new Image();
			$image->Name = $this->Title;
			$image->Title = $this->Title;
			$image->Filename = $this->Filename;

			$photo->InitiallySortedByFilename = false;

			// this publishes to staging, not live, which is what we want
			$photo->Sort = 100000+$this->SortOrder;
			$photo->SortOrder = $photo->Sort;

			$image->write();
			$photo->PhotoID = $image->ID;



		

			//DEBUGLOG("PHOTO SORT T1:".$photo->Sort);


			$photo->write();

			// in effect mark th is for deletion
			$this->PhotoID = $photo->ID;
			//DEBUGLOG("PHOTO SORT T2:".$photo->Sort);

			//$photo->write();
			$this->returnItemToUser($photo);





		} else {
			//DEBUGLOG("IF: T3");

		}
		
	}
 
}
?>

