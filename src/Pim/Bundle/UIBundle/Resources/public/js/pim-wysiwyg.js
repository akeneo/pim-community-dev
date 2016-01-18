/* global tinymce */
define(
    ['jquery', 'underscore', 'backbone', 'tinymce'],
    function ($, _, Backbone) {
        'use strict';

        var config = {
            plugins:   'link preview code paste',
            statusbar: true,
            menubar:   false,
            toolbar:   'bold italic underline strikethrough | bullist numlist | outdent indent | link | preview code',
            readonly:  false,
            setup:     function (ed) {
                var $el = $('#' + ed.id);
                $el.data('value', $el.val());
                ed.on('change', function () {
                    $el.data('dirty', true);
                    $el.trigger('change');
                });
                ed.on('saveContent', function (e) {
                    if (true !== $el.data('dirty')) {
                        e.content = $el.data('value');
                    }
                });
            }
        };
        var destroyEditor = function (id) {
            var instance = tinymce.get(id);
            if (instance) {
                tinymce.execCommand('mceRemoveControl', true, id);
                tinymce.remove(instance);

                var $textarea = $('#' + id);
                $textarea.show();
            }
        };

        var isAlreadyRendered = function (id) {
            for (var i = tinymce.editors.length - 1; i >= 0; i--) {
                if (tinymce.editors[i].id === id) {
                    return true;
                }
            }

            return false;
        };

        Backbone.Router.prototype.on('route', function () {
            for (var i = tinymce.editors.length - 1; i >= 0; i--) {
                destroyEditor(tinymce.editors[i].id);
            }
        });

        return {
            settings: [],
            init: function ($el, options) {

                var id = $el.attr('id');

                this.settings[id] = _.extend(
                    _.clone(config),
                    { selector: '#' + id, readonly: $el.is('[disabled]') },
                    options
                );

                // Avoid to bind several times the 'onClick' action, or tinymce will be instantiated
                // X times on the same element.
                if (!$el.data('bind-wysiwyg')) {

                    $el.on('click', _.bind(function () {
                        if (!isAlreadyRendered(id)) {
                            tinymce.init(this);
                            setTimeout(_.bind(function () {
                                tinymce.execCommand('mceFocus', true, this);
                            }, this), 200);
                        }
                    }, this.settings[id]));

                    // Mark this textarea as already bind
                    $el.data('bind-wysiwyg', true);
                }

                return this;
            },
            destroy: function ($el) {
                destroyEditor($el.attr('id'));

                return this;
            },
            reinit: function ($el) {
                var id = $el.attr('id');
                this.settings = tinymce.editors[id] ? tinymce.editors[id].settings : {};

                return this.destroy($el).init($el, this.settings);
            },
            readonly: function ($el, state) {
                this.destroy($el).init($el, { readonly: state });

                return this;
            }
        };
    }
);
