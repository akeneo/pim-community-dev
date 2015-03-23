'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/panel/comments',
        'text!pim/template/product/panel/comment-reply',
        'routing',
        'oro/messenger'
    ],
    function (_, Backbone, BaseForm, template, replyForm, Routing, messenger) {
        return BaseForm.extend({
            template: _.template(template),
            replyFormTemplate: _.template(replyForm),
            className: 'panel-pane',
            code: 'history',
            comments: [],
            events: {
                'keyup .create-comment textarea': 'toggleButtons',
                'click .create-comment .comment-btn.btn-primary': 'saveComment',
                'click .remove-comment' : 'removeComment',
                'click .reply-to-comment' : 'showReplyForm',
                'click .comment-reply input[type="reset"]' : 'hideReplyForm',
                'click .comment-reply .comment-btn.btn-primary': 'saveReply'
            },
            initialize: function () {
                this.comment = new Backbone.Model();

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addPanel('comments', 'Comments');

                this.$replyForm = $(this.replyFormTemplate());

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return;
                }

                this.loadData().done(_.bind(function (data) {
                    this.comments = data;

                    this.$el.html(
                        this.template({
                            comments: this.comments
                        })
                    );
                    this.delegateEvents();
                }, this));

                return this;
            },
            loadData: function () {
                return $.get(
                    Routing.generate(
                        'pim_enrich_product_comments_rest_get',
                        {
                            id: this.getData().meta.id
                        }
                    )
                );
            },
            toggleButtons: function () {
                if (this.$('form.create-comment textarea').val()) {
                    this.$('form.create-comment .comment-buttons').show();
                } else {
                    this.$('form.create-comment .comment-buttons').hide();
                }
            },
            saveComment: function () {
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_enrich_product_comments_rest_post', { id: this.getData().meta.id }),
                    contentType: 'application/json',
                    data: JSON.stringify({ 'body': this.$('form.create-comment textarea').val() })
                }).done(_.bind(function () {
                    this.render();
                    messenger.notificationFlashMessage('success', 'Your comment has been created successfully.');
                }, this)).fail(function () {
                    messenger.notificationFlashMessage('error', 'An error occured during the creation of your comment.');
                });
            },
            removeComment: function (event) {
                $.ajax({
                    url: Routing.generate('pim_comment_comment_delete', { id: event.currentTarget.dataset.commentId }),
                    type: 'POST',
                    headers: { accept:'application/json' },
                    data: { _method: 'DELETE' }
                }).done(_.bind(function () {
                    this.render();
                    messenger.notificationFlashMessage('success', 'Your comment has been deleted successfully.');
                }, this)).fail(function () {
                    messenger.notificationFlashMessage('error', 'An error occured during the deletion of your comment.');
                });
            },
            showReplyForm: function (event) {
                this.$replyForm.data('comment-id', event.currentTarget.dataset.commentId);
                this.$replyForm.appendTo($(event.currentTarget).closest('.comment-thread').find('.comment-topic')).show();
            },
            hideReplyForm: function () {
                this.$replyForm.find('textarea').val('');
                this.$replyForm.hide();
            },
            saveReply: function () {
                $.ajax({
                    type: 'POST',
                    url: Routing.generate(
                        'pim_enrich_product_comment_reply_rest_post',
                        {
                            id: this.getData().meta.id,
                            commentId: this.$replyForm.data('comment-id')
                        }
                    ),
                    contentType: 'application/json',
                    data: JSON.stringify({ 'body': this.$replyForm.find('textarea').val()})
                }).done(_.bind(function () {
                    this.$replyForm.find('textarea').val('');
                    this.render();
                    messenger.notificationFlashMessage('success', 'Your reply has been created successfully.');
                }, this)).fail(function () {
                    messenger.notificationFlashMessage('error', 'An error occured during the creation of your reply.');
                });
            }
        });
    }
);
