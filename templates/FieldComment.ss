<div class="fieldcomment">
    <div class="wrapper">
        <p class="controls">
            <span class="mark-read">
                <a href="javascript:void(0)">Mark all as read</a>
			</span>
            <span class="expand">
				<a href="javascript:void(0)">Expand all comments</a>
			</span>
        </p>
        <div class="comments">
            <ul>
                <% if $fieldComments %>
                <% loop $fieldComments %>
                    <li data-fieldcomment-id="$ID" class="comment <% if $Opened %>read<% else %>unread<% end_if %>">
                    <span class="envelop"></span>
                    <span class="comment-summary">$Summary</span>
                    <span class="comment-full hidden">$Comment</span>
                    <span class="meta">$CreatedNice $FirstName $Surname</span>
                </li>
               <% end_loop %>
                <% else %>
                <li>No comment to display for this field</li>
                <% end_if %>
            </ul>
        </div>
        <div class="add-comment">
            <button class="add-action">Add a comment</button>
        </div>
    </div>
</div>