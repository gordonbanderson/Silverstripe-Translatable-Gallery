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




			//$this->delete();
/*

public function returnItemToUser($p) {
		if(Director::is_ajax()) {
			// Prepare the object for insertion.
			$parentID = (int) $p->ParentID;
			$id = $p->ID ? $p->ID : "new-$p->class-$p->ParentID";
			$treeTitle = Convert::raw2js($p->TreeTitle());
			$hasChildren = (is_numeric($id) && $p->AllChildren() && $p->AllChildren()->Count()) ? ' unexpanded' : '';


*/


		// now we delete the bulk uploaded image objects - note the files themselves are not deleted
		//$imageFiles = DataObject::get('ImageFile', 'GalleryID='.$this->GalleryID);
		/*
		foreach($imageFiles as $imageFile) {
			$imageFile->destroy();
		}
*/


		/*
		mysql> select * from File where ID=91;
+----+-----------+---------------------+---------------------+---------+-------+------------------------+---------+------+-----------+----------+---------+
| ID | ClassName | Created             | LastEdited          | Name    | Title | Filename               | Content | Sort | SortOrder | ParentID | OwnerID |
+----+-----------+---------------------+---------------------+---------+-------+------------------------+---------+------+-----------+----------+---------+
| 91 | Image     | 2011-05-05 10:36:52 | 2011-05-05 10:36:52 | cb2.png | cb2   | assets/Uploads/cb2.png | NULL    |    0 |        68 |        3 |       0 |
+----+-----------+---------------------+---------------------+---------+-------+------------------------+---------+------+-----------+----------+---------+
1 row in set (0.00 sec)

*/
		}
		
	}
 
}
?>
