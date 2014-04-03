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
                ed.on('change', function() {
                    $('#' + ed.id).trigger('change');
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
            init: function(id, options) {
                var settings = _.extend(_.clone(config), options, { selector: '#' + id });
                tinymce.init(settings);

                return this;
            },
            destroy: function(id) {
                destroyEditor(id);

                return this;
            },
            reinit: function(id) {
                var settings = tinymce.editors[id] ? tinymce.editors[id].settings : { readonly: $('#' + id).is('[disabled]') };

                return this.destroy(id).init(id, settings);
            },
            readonly: function(id, state) {
                this.destroy(id).init(id, { readonly: state });

                return this;
            }
        };
    }
);
