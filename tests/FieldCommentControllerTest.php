<?php

namespace silverstripe\fieldComment\tests;

use FunctionalTest;
use Security;
use silverstripe\fieldComment\tests\DecoratedDataObject;
use DataObject;

class FieldCommentControllerTest extends FunctionalTest
{
    protected $extraDataObjects = array(
        'silverstripe\fieldComment\tests\DecoratedDataObject'
    );

    /**
     * post comment
     * @test
     */
    public function PostComment()
    {
        $response = $this->post('fieldcomment/postComment', array());
        $this->assertEquals($response->getStatusCode(), 401);
        $this->logInAs(Security::findAnAdministrator());

        $decoratedDataObject = new DecoratedDataObject();
        $decoratedDataObject->write();
        $response = $this->post(
            'fieldcomment/postComment',
            array(
                'Comment' => 'Test comment',
                'CommentClassName' => $decoratedDataObject->ClassName,
                'CommentDataObjectID' => $decoratedDataObject->ID,
                'CommentFieldName' => 'FieldA'
            )
        );
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertNotNull(DataObject::get_one('silverstripe\fieldComment\FieldComment', sprintf('"CommentDataObjectID" = %d AND "CommentClassName"=\'DecoratedDataObject\'', $decoratedDataObject->ID)));
    }

    /**
     * append html template to field
     * @test
     */
    public function LoadTemplate()
    {
        $decoratedDataObject = DecoratedDataObject::get()->first();
        $this->logInAs(Security::findAnAdministrator());
        $response = $this->get(
            'fieldcomment/LoadTemplate?' .
            http_build_query(
                array(
                    'CommentClassName' => $decoratedDataObject->ClassName,
                    'CommentDataObjectID' => $decoratedDataObject->ID,
                    'CommentFieldName' => 'FieldA'
                )
            )
        );
        $this->assertContains('Test comment', $response->getBody());
    }
    /**
     * get all the commenatable field in DataObject
     * @test
     */
    public function GetCommentableFields()
    {
        $decoratedDataObject = DecoratedDataObject::get()->first();
        $this->logInAs(Security::findAnAdministrator());
        $response = $this->get(
            'fieldcomment/getFields?' .
            http_build_query(
                array(
                    'CommentClassName' => $decoratedDataObject->ClassName
                )
            )
        );
        $this->assertContains('{"Fields":["FieldA","FieldBID"]}', $response->getBody());
    }
}
