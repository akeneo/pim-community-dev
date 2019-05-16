'use strict';
/**
 * Comment panel extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'pim/user-context',
        'pim/template/product/comments',
        'routing',
        'oro/messenger',
        'pim/dialog'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        UserContext,
        template,
        Routing,
        messenger,
        Dialog
    ) {
        return BaseForm.extend({
            template: _.template(template),

            className: 'panel-pane',

            comments: [],

            events: {
                'keyup .comment-create textarea, .reply-to-comment textarea': 'toggleButtons',
                'click .comment-create .send-comment': 'saveComment',
                'click .remove-comment': 'removeComment',
                'click .comment-thread .send-comment': 'saveReply',
                'click .comment-thread .cancel-comment, .comment-create .cancel-comment': 'cancelComment'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.comment = new Backbone.Model();

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: __('pim_enrich.entity.product.module.comment.title')
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured || this.code !== this.getParent().getCurrentTab()) {
                    return this;
                }

                this.loadData().done(function (data) {
                    this.comments = data;

                    this.$el.html(
                        this.template({
                            __: __,
                            comments: this.comments,
                            username: UserContext.get('username'),
                            removedAuthor: '[' + __('pim_enrich.entity.product.module.comment.removed_author') + ']',
                            emptyLabel: __('pim_enrich.entity.product.module.comment.empty')
                        })
                    );
                    this.delegateEvents();
                }.bind(this));

                return this;
            },

            /**
             * Load the comments from database
             *
             * @return {Promise}
             */
            loadData: function () {
                return $.get(
                    Routing.generate(
                        'pim_enrich_product_comments_rest_get',
                        {
                            id: this.getFormData().meta.id
                        }
                    )
                );
            },

            /**
             * Display or hide the save/cancel buttons attached to the text area
             *
             * @param {Event} event
             */
            toggleButtons: function (event) {
                var $element = $(event.currentTarget).parents('.comment-thread, .comment-create');
                if ($element.find('textarea').val()) {
                    $element.addClass('active');
                    $element.find('.AknButtonList').removeClass('AknButtonList--hide');
                } else {
                    $element.removeClass('active');
                    $element.find('.AknButtonList').addClass('AknButtonList--hide');
                }
            },

            /**
             * Cancels the current written comment
             *
             * @param {Event} event
             */
            cancelComment: function (event) {
                var $element = $(event.currentTarget).parents('.comment-thread, .comment-create');
                $element.find('textarea').val('');
                $element.removeClass('active');
                $element.find('.AknButtonList').addClass('AknButtonList--hide');
            },

            /**
             * Saves the current comment to database
             */
            saveComment: function () {
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_enrich_product_comments_rest_post', { id: this.getFormData().meta.id }),
                    contentType: 'application/json',
                    data: JSON.stringify({ 'body': this.$('.comment-create textarea').val() })
                }).done(function () {
                    this.render();
                    messenger.notify('success', __('flash.comment.create.success'));
                }.bind(this)).fail(function () {
                    messenger.notify('error', __('flash.comment.create.error'));
                });
            },

            /**
             * Shows a confirm dialog before removing the current comment
             *
             * @param {Event} event
             */
            removeComment: function (event) {
                Dialog.confirmDelete(
                    __('pim_enrich.entity.product.module.comment.delete_confirm'),
                    __('pim_common.confirm_deletion'),
                    this.doRemove.bind(this, event),
                );
            },

            /**
             * Removes the comment from database
             *
             * @param {Event} event
             */
            doRemove: function (event) {
                $.ajax({
                    url: Routing.generate('pim_comment_comment_delete', { id: event.currentTarget.dataset.commentId }),
                    type: 'POST',
                    headers: { accept: 'application/json' },
                    data: { _method: 'DELETE' }
                }).done(function () {
                    this.render();
                    messenger.notify('success', __('flash.comment.delete.success'));
                }.bind(this)).fail(function () {
                    messenger.notify('error', __('flash.comment.delete.error'));
                });
            },

            /**
             * Save the current reply of a comment
             *
             * @param {Event} event
             */
            saveReply: function (event) {
                var $thread = $(event.currentTarget).parents('.comment-thread');

                $.ajax({
                    type: 'POST',
                    url: Routing.generate(
                        'pim_enrich_product_comment_reply_rest_post',
                        {
                            id: this.getFormData().meta.id,
                            commentId: $thread.data('comment-id')
                        }
                    ),
                    contentType: 'application/json',
                    data: JSON.stringify({ 'body': $thread.find('textarea').val()})
                }).done(function () {
                    $thread.find('textarea').val('');
                    this.render();
                    messenger.notify('success', __('flash.comment.reply.success'));
                }.bind(this)).fail(function () {
                    messenger.notify('error', __('flash.comment.reply.error'));
                });
            }
        });
    }
);
