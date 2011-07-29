<?
class ImageFile extends File {
 
	static $has_one = array (
		'Gallery' => 'Gallery'
	);


	/**
	 * Allows you to returns a new data object to the tree (subclass of sitetree)
	 * and updates the tree via javascript.
	 */
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
				newNode.selectTreeNode();
JS;
			FormResponse::add($response);

			return FormResponse::respond();
		} else {
			Director::redirect('admin/' . self::$url_segment . '/show/' . $p->ID);
		}
	}



	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		
		error_log("OBW: Image file");
		error_log(print_r($this,1));

		// create a photograph using these details

		if ($this->GalleryID) {
			error_log("Creating photograph");

			$photo = new Photograph();
			$photo->Title = $this->Title;
			$photo->ParentID = $this->GalleryID;

			error_log("About to save photo");

			$image = new Image();
			$image->Name = $this->Title;
			$image->Title = $this->Title;
			$image->Filename = $this->Filename;
			$image->write();

			$photo->PhotoID = $image->ID;
			$photo->write();



			$photo->write();
			error_log("Saved photo");



			$this->returnItemToUser($photo);
		}
		
	}
 
}
?>
