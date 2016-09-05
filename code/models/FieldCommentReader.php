<?php
namespace silverstripe\fieldComment;

use DataObject;

class FieldCommentReader extends DataObject
{

    /**
     * @var array
     */
    private static $has_one = array(
        'Reader' => 'Member',
        'FieldComment' => 'silverstripe\fieldComment\FieldComment'
    );
}
