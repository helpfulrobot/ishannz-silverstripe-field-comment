<?php
namespace silverstripe\fieldComment\tests;

use DataObject;
use TestOnly;
use GridField;
use Member;

class DecoratedDataObject extends DataObject implements TestOnly
{

    static $db = array(
        'FieldA' => 'VarChar'
    );

    static $has_one = array(
        'FieldB' => 'Member'
    );

    static $commentable_fields = array(
        'FieldA',
        'FieldBID'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', new GridField('FieldBID', 'Member', Member::get()));
        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

}
