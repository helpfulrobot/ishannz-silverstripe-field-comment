<?php
namespace silverstripe\fieldComment;

use controller;
use SS_HTTPRequest;
use Member;
use Permission;
use silverstripe\fieldComment\FieldComment;
use silverstripe\fieldComment\FieldCommentReader;
use ArrayData;
use DataObject;

/**
 * Controller used for CRUD operation with comments
 */
class FieldCommentController extends Controller
{
    /**
     * @var array
     */
    private static $allowed_actions = array(
        'postComment',
        'loadComments',
        'markAsRead',
        'loadTemplate',
        'getFields',
    );

    /**
     * @param null $member
     * @return bool
     */
    public function canView($member = null)
    {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUserID();
        }
        return ($member && Permission::checkMember($member, "CMS_ACCESS_CMSMain"));
    }

    /**
     * @param null $member
     * @return bool
     */
    public function canCreate($member = null)
    {
        return $this->canView();
    }

    /**
     * Write a new comment for a particular field of a DataObject record
     * @param SS_HTTPRequest $request
     * @return void
     */
    public function postComment(SS_HTTPRequest $request)
    {
        if (!$this->canCreate()) return $this->httpError(401);
        $vars = $request->requestVars();
        $fieldComment = new FieldComment();
        $fieldComment->Comment = $vars['Comment'];
        $targetDataObject = DataObject::get_by_id($vars['CommentClassName'], $vars['CommentDataObjectID']);
        if (!$targetDataObject) {
            return $this->httpError(404);
        }
        $fieldComment->CommentClassName = $vars['CommentClassName'];
        $fieldComment->CommentDataObjectID = $vars['CommentDataObjectID'];
        $fieldComment->CommentFieldName = $vars['CommentFieldName'];
        $fieldComment->AuthorID = Member::currentUserID();
        $fieldComment->write();
    }

    /**
     * Return the full length comment
     * @param SS_HTTPRequest $request
     * @return string
     */
    public function markAsRead(SS_HTTPRequest $request)
    {
        if (!$this->canView()) return $this->httpError(401);
        $commentIDs = $request->postVar('CommentIDs');
        if (!$commentIDs) return;
        $fieldComments = FieldComment::get()->byIDs($commentIDs);
        foreach ($fieldComments as $fieldComment) {
            if ($fieldComment->AuthorID != Member::currentUserID() &&
                !in_array(Member::currentUserID(), $fieldComment->Readers()->column('ReaderID'))
            ) {
                $fieldCommentReader = new FieldCommentReader();
                $fieldCommentReader->ReaderID = Member::currentUserID();
                $fieldComment->Readers()->add($fieldCommentReader->write());
                $fieldComment->write();
            }
        }
    }

    /**
     * load template to form
     * @param SS_HTTPRequest $request
     * @return HTMLText|void
     * @throws SS_HTTPResponse_Exception
     */
    public function loadTemplate(SS_HTTPRequest $request)
    {
        if (!$this->canView()) return $this->httpError(401);

        $vars = $request->requestVars();
        $FieldComment = new FieldComment();
        $fieldComments = $FieldComment->getComments($vars['CommentClassName'], $vars['CommentDataObjectID'], $vars['CommentFieldName']);

        return $this->customise(new ArrayData(array(
            'fieldComments' => $fieldComments
        )))->renderWith("FieldComment");
    }

    /**
     * get comentable fields
     * @param SS_HTTPRequest $request
     * @return string
     */
    public function getFields(SS_HTTPRequest $request)
    {
        if (!$this->canView()) return $this->httpError(401);

        $vars = $request->requestVars();
        $ClassName = $vars['CommentClassName'];
        $classObj = new $ClassName();
        $fields = $classObj->getCMSFields();
        $fieldIDs = array();

        $commentables = $classObj->owner->stat('commentable_fields');

        foreach ($fields->dataFields() as $field) {
            if (is_object($field) && $field->getName()) {
                if (in_array($field->getName(), $commentables)) {
                    $fieldIDs['Fields'][] = $field->getName();
                }
            }
        }

        return json_encode($fieldIDs);
    }
}
