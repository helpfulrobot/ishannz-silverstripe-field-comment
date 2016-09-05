<?php
namespace silverstripe\fieldComment;

use DataObject;
use Member;
use Permission;
use ArrayList;
use Convert;
use silverstripe\fieldComment\FieldCommentReader;
use ArrayData;

class FieldComment extends DataObject
{
    /**
     * @var array
     */
    private static $db = array(
        'Comment' => 'Text',
        'CommentClassName' => 'Varchar(255)',
        'CommentDataObjectID' => 'Int',
        'CommentFieldName' => 'Varchar(255)'
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'Author' => 'Member'
    );

    /**
     * @var array
     */
    private static $has_many = array(
        'Readers' => 'silverstripe\fieldComment\FieldCommentReader'
    );

    /**
     * @var string
     */
    private static $default_sort = 'Created DESC';

    /**
     * @var array
     */
    private static $indexes = array(

        'field_identifier' => array(
            'type' => 'index',
            'value' => '"CommentClassName","CommentDataObjectID","CommentFieldName"'
        )
    );

    /**
     * @param null $member
     * @return bool|null
     */
    public function canCreate($member = null)
    {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUserID();
        }

        if ($member && Permission::checkMember($member, "CMS_ACCESS_CMSMain")) return true;

        // Standard mechanism for accepting permission changes from decorators
        $extended = $this->extendedCan('canCreate', $member);
        if ($extended !== null) return $extended;

        return $this->stat('can_create') != false || Director::isDev();
    }

    /**
     * @param null $member
     * @return bool|null
     */
    public function canView($member = null)
    {
        return $this->canCreate($member);
    }

    /**
     * @return mixed
     */
    public function getCurrentReader()
    {
        return $this->Readers()->filter('ReaderID', Member::currentUserID())->first();
    }

    /**
     * @param $className
     * @param $ID
     * @param null $fieldName
     * @return ArrayList
     */
    public function getComments($className, $ID, $fieldName = null)
    {
        $FieldComment = SELF::get()->filter(array(
            'CommentClassName' => $className,
            'CommentDataObjectID' => (int)$ID,
            'CommentFieldName' => Convert::raw2sql($fieldName),
        ))->sort('Created');

        $comments = new ArrayList();
        foreach ($FieldComment as $comment) {
            $Author = $comment->Author();
            $Reader = $comment->getCurrentReader();

            $comment = $comment->toMap();
            $comment['Summary'] = (isset($comment['Comment'])) ? substr($comment['Comment'], 0, 10) : '';
            $comment['CreatedNice'] = date("d M Y  g:i A", strtotime($comment['Created']));
            $comment['Opened'] = ($comment['AuthorID'] == Member::currentUserID()) || ($Reader && ($Reader->ReaderID == Member::currentUserID()));
            $comment['FirstName'] = $Author->FirstName;
            $comment['Surname'] = $Author->Surname;
            $comment['FieldCommentID'] = ($Reader) ? $Reader->FieldCommentID : '';
            $comment['ReaderID'] = ($Reader) ? $Reader->ReaderID : '';

            $comments->push(
                new ArrayData($comment)
            );
        }

        return $comments;
    }

    /**
     * @param $ClassName
     * @param $DataObjectID
     * @return string
     */
    public function getFields($ClassName, $DataObjectID)
    {
        $FieldComment = SELF::get()->filter(array(
            'CommentClassName' => Convert::raw2sql($ClassName),
            'CommentDataObjectID' => (int)$DataObjectID
        ))->sort('Created');

        return json_encode($FieldComment->toNestedArray());

    }
}
