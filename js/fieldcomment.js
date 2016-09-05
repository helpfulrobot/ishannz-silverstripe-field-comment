(function($) {
	$(document).ready(function() {

		var CommentClassName;
		var CommentDataObjectID;
		var form;

		$('input[name=CommentClassName]').entwine({
			onmatch: function(e){
				CommentClassName = $(this).val();
				CommentDataObjectID = $('input[name=CommentDataObjectID]').val();
				form = $('input[name=CommentClassName]').closest('form');

				$.get( "fieldcomment/getFields",{ CommentClassName: CommentClassName }, function( data ) {
					var $commentableFields = $.parseJSON(data);
					var total = $($commentableFields['Fields']).length;
					$($commentableFields['Fields']).each(function(index, CommentField){
						var last = false;
						if (index === total - 1) {
							last = true;
						}
						$(this).reloadComments(CommentField,last);
					});

				});

			}
		});

		//get unread messages count
		$.fn.unreadMessages = function() {
			var msgCount =  $("li.unread").length;
			$('p.message', form).addClass('notice').html('<span class="fieldcomment-feedback">You have <span class="unread-comments-count">'+msgCount+'</span> unread messages.</span>').show();
		};

		//load the comments
		$.fn.reloadComments = function(CommentField,last){
			$.get( "fieldcomment/loadTemplate",{ CommentClassName: CommentClassName, CommentDataObjectID: CommentDataObjectID, CommentFieldName:CommentField }, function( html ) {
				var field = $('fieldset.field#'+form.attr('id')+'_'+CommentField).length ? $('fieldset.field#'+form.attr('id')+'_'+CommentField) : ($('#Form_EditForm_'+CommentField+'_Holder').length ? $('#Form_EditForm_'+CommentField+'_Holder') : $('#'+CommentField));
				if (field.length == 0){
					return;
				}
				field.find('.fieldcomment').remove();
				field.append(html);
				field.data('fieldname', CommentField);

				if(last === true){
					$(this).unreadMessages();
					$(this).AddComment();
				}
			});
		};

		$.fn.AddComment = function(){
			$('.fieldcomment .add-comment .add-action').click(function(e) {
				e.preventDefault();
				var fieldComment = this.closest('.fieldcomment');
				if ($('.new-comment', fieldComment).length == 0){
					$('.add-comment', fieldComment).append('' +
						'<div class="new-comment">' +
						'<label class="left" for="Form_EditForm_Content">Comment</label>' +
						'<div class="middleColumn"><textarea class="commentText" required /></div><button class="post-action">Post comment</button></div>');
					$(this).hide();
				}
				$(this).postComment();

			});

		};

		$.fn.postComment = function(){
			$('.fieldcomment .add-comment .post-action').click(function(e) {

				var form = this.closest('form');
				var fieldHolder = this.closest('.field');
				var CommentField = $(fieldHolder).data('fieldname');

				$.ajax({
					url: 'fieldcomment/postComment',
					type: "POST",
					data: {
						'Comment': $(this).closest('.field').find('.commentText').val(),
						'CommentClassName': CommentClassName,
						'CommentDataObjectID': CommentDataObjectID,
						'CommentFieldName': CommentField
					},
					dataType: 'html',
					success: function(data){
						$(this).closest('.new-comment').hide();
						$(this).reloadComments(CommentField,true);
					},
					error: function (jqXHR, textStatus) {
						statusMessage(jqXHR.statusText, "bad");
					}
				});

			});

		};

		$('.fieldcomment .expand').entwine({
			onclick: function(e){
				e.preventDefault();
				var fieldComment = this.closest('.fieldcomment');
				$('.comment-summary').toggle();
				$('.comment-full').toggle();
				this.toggleClass('expanded');
				$('a', this).html(this.hasClass('expanded') ? 'Collapse all comments' : 'Expand all comments');
			}
		});

		$('.fieldcomment .mark-read').entwine({
			onclick: function(e){
				e.preventDefault();
				var fieldHolder = this.closest('.field');
				var CommentField = $(fieldHolder).data('fieldname');
				var fieldComment = this.closest('.fieldcomment');
				var form = this.closest('form');
				var commentIDs = [];
				$('li.comment.unread', fieldComment).each(function(index,comment){
					commentIDs.push($(comment).attr('data-fieldcomment-id'));
				});
				$.ajax({
					url: 'fieldcomment/markAsRead',
					type: "POST",
					data: {
						'CommentIDs': commentIDs
					},
					success: function(data){
						fieldComment.find('.wrapper').reloadComments(CommentField,true);
					},
					error: function (jqXHR, textStatus) {
						statusMessage(jqXHR.statusText, "bad");
					}
				});
			}
		});

		$('.fieldcomment .comments').entwine({
			onmatch: function(e){
				var fieldComment = this.closest('.fieldcomment');
				if ($('ul li.comment', this).length){
					$('.expand', fieldComment).show();
				} else {
					$('.expand', fieldComment).hide();
				}
				if ($('ul li.comment.unread', this).length){
					$('.mark-read', fieldComment).show();
				} else {
					$('.mark-read', fieldComment).hide();
				}
			}
		});

	});

})(jQuery);
