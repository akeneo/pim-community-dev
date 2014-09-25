/* global tinymce */
define(
    ['jquery', 'underscore', 'backbone', 'tinymce'],
    function($, _, Backbone) {
        'use strict';

        var config = {
            plugins:   'link preview code paste',
            statusbar: true,
            menubar:   false,
            toolbar:   'bold italic underline strikethrough | bullist numlist | outdent indent | link | preview code',
            readonly:  false,
            setup:     function(ed) {
                var $el = $('#' + ed.id);
                $el.data('value', $el.val());
                ed.on('change', function() {
                    $el.data('dirty', true);
                    $el.trigger('change');
                });
                ed.on('saveContent', function(e) {
                    if (true !== $el.data('dirty')) {
                        e.content = $el.data('value');
                    }
                });
            }
        };
        var destroyEditor = function(id) {
            var instance = tinymce.get(id);
            if (instance) {
                tinymce.remove(instance);
                tinymce.execCommand('mceRemoveControl', true, id);
            }
        };
        Backbone.Router.prototype.on('route', function () {
            for (var i = tinymce.editors.length - 1; i >= 0; i--) {
                destroyEditor(tinymce.editors[i].id);
            }
        });
        return {
            init: function($el, options) {
                var settings = _.extend(
                    _.clone(config),
                    { selector: '#' + $el.attr('id'), readonly: $el.is('[disabled]') },
                    options
                );
                tinymce.init(settings);

                return this;
            },
            destroy: function($el) {
                destroyEditor($el.attr('id'));

                return this;
            },
            reinit: function($el) {
                var id = $el.attr('id');
                var settings = tinymce.editors[id] ? tinymce.editors[id].settings : {};

                return this.destroy($el).init($el, settings);
            },
            readonly: function($el, state) {
                this.destroy($el).init($el, { readonly: state });

                return this;
            }
        };
    }
);
