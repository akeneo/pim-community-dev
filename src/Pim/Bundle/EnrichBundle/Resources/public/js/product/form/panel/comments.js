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
        'backbone',
        'pim/form',
        'pim/user-context',
        'text!pim/template/product/panel/comments',
        'routing',
        'oro/messenger',
        'pim/dialog'
    ],
    function ($, _, Backbone, BaseForm, UserContext, template, Routing, messenger, Dialog) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-pane',
            comments: [],
            events: {
                'keyup .comment-create textarea, .reply-to-comment textarea': 'toggleButtons',
                'click .comment-create .send-comment': 'saveComment',
                'click .remove-comment': 'removeComment',
                'click .comment-thread .send-comment': 'saveReply',
                'click .comment-thread .cancel-comment': 'cancelComment'
            },
            initialize: function () {
                this.comment = new Backbone.Model();

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.trigger('panel:register', {
                    code: this.code,
                    label: _.__('pim_comment.product.panel.comment.title')
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured || this.code !== this.getParent().state.get('currentPanel')) {
                    return this;
                }

                this.loadData().done(function (data) {
                    this.comments = data;

                    this.$el.html(
                        this.template({
                            comments: this.comments,
                            currentUser: UserContext.toJSON()
                        })
                    );
                    this.delegateEvents();
                }.bind(this));

                return this;
            },
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
            toggleButtons: function (event) {
                var $element = $(event.currentTarget).parents('.comment-thread, .comment-create');
                if ($element.find('textarea').val()) {
                    $element.addClass('active');
                } else {
                    $element.removeClass('active');
                }
            },
            cancelComment: function (event) {
                var $element = $(event.currentTarget).parents('.comment-thread, .comment-create');
                $element.find('textarea').val('');
                $element.removeClass('active');
            },
            saveComment: function () {
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_enrich_product_comments_rest_post', { id: this.getFormData().meta.id }),
                    contentType: 'application/json',
                    data: JSON.stringify({ 'body': this.$('.comment-create textarea').val() })
                }).done(function () {
                    this.render();
                    messenger.notificationFlashMessage('success', _.__('flash.comment.create.success'));
                }.bind(this)).fail(function () {
                    messenger.notificationFlashMessage('error', _.__('flash.comment.create.error'));
                });
            },
            removeComment: function (event) {
                Dialog.confirm(
                    _.__('confirmation.remove.comment'),
                    _.__('pim_enrich.confirmation.delete_item'),
                    this.doRemove.bind(this, event)
                );
            },
            doRemove: function (event) {
                $.ajax({
                    url: Routing.generate('pim_comment_comment_delete', { id: event.currentTarget.dataset.commentId }),
                    type: 'POST',
                    headers: { accept: 'application/json' },
                    data: { _method: 'DELETE' }
                }).done(function () {
                    this.render();
                    messenger.notificationFlashMessage('success', _.__('flash.comment.delete.success'));
                }.bind(this)).fail(function () {
                    messenger.notificationFlashMessage('error', _.__('flash.comment.delete.error'));
                });
            },
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
                    messenger.notificationFlashMessage('success', _.__('flash.comment.reply.success'));
                }.bind(this)).fail(function () {
                    messenger.notificationFlashMessage('error', _.__('flash.comment.reply.error'));
                });
            }
        });
    }
);
