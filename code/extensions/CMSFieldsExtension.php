<?php
namespace silverstripe\fieldComment;

use DataExtension;
use FieldList;
use HiddenField;
use silverstripe\fieldComment\FieldComment;
use DB;

class CMSFieldsExtension extends DataExtension
{

    protected $comments = null;

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        if (!$fields->fieldByName('CommentClassName')) {
            $fields->push(new HiddenField('CommentClassName', 'CommentClassName', $this->owner->ClassName));
        }
        if (!$fields->fieldByName('CommentDataObjectID')) {
            $fields->push(new HiddenField('CommentDataObjectID', 'CommentDataObjectID', $this->owner->ID));
        }

    }

    /**
     * Delete all comments associated with a page after its publication
     *
     */
    public function onAfterPublish()
    {
        $comments = FieldComment::get()->filter(array(
            'CommentClassName' => $this->owner->ClassName,
            'CommentDataObjectID' => $this->owner->ID
        ));

        if ($comments && $comments->Count()) {
            foreach ($comments as $comment) {
                DB::query('DELETE FROM "silverstripe\fieldComment\FieldCommentReader" WHERE "FieldCommentID" = ' . $comment->ID);
                $comment->delete();
            }
        }
    }

}
