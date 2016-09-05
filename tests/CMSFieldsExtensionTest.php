<?php
namespace silverstripe\fieldComment;

use SapphireTest;
use silverstripe\fieldComment\tests\DecoratedDataObject;


class CMSFieldsExtensionTest extends SapphireTest
{
    /**
     * update cms fields
     * @test
     */
    public function UpdateCMSFields()
    {
        $decoratedDataObject = new DecoratedDataObject();
        $decoratedDataObject->ID = 999;
        $fields = $decoratedDataObject->getCMSFields();
        $commentClassName = $fields->fieldByName('CommentClassName');
        $this->assertEquals($commentClassName->value, $decoratedDataObject->ClassName);
        $commentDataObjectID = $fields->fieldByName('CommentDataObjectID');
        $this->assertEquals($commentDataObjectID->value, $decoratedDataObject->ID);
    }
}
