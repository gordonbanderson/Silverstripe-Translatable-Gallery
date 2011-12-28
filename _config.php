<?php

//DataObjectManager_Controller/dosort
// cant override DOM JS, so override at the routing level
Director::addRules(100, array('DataObjectManager_Controller/$Action/Photograph' => 'PhotographDataObject_Controller'));
Director::addRules(100, array('fbimport/$Action/$ID' => 'Gallery_Controller'));


Object::add_extension('CMSMain', 'GalleryCMSActionDecorator');
?>
