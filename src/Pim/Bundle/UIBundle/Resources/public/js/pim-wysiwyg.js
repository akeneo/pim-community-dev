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
                    var $el = $('#' + ed.id);
                    if (!_.isUndefined($el.data('disabled'))) {
                        $el.prop('disabled', $el.data('disabled'));
                    }
                    $el.trigger('change');
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
            init: function($el, options, forceSubmit) {
                var disabled = $el.is('[disabled]');
                if (!forceSubmit) {
                    $el.data('disabled', disabled).prop('disabled', true);
                }
                var settings = _.extend(_.clone(config), { selector: '#' + $el.attr('id'), readonly: disabled }, options);
                tinymce.init(settings);

                return this;
            },
            destroy: function($el) {
                if (!_.isUndefined($el.data('disabled'))) {
                    $el.prop('disabled', $el.data('disabled'));
                }
                destroyEditor($el.attr('id'));

                return this;
            },
            reinit: function($el, forceSubmit) {
                var id = $el.attr('id');
                var settings = tinymce.editors[id] ? tinymce.editors[id].settings : {};

                return this.destroy($el).init($el, settings, forceSubmit);
            },
            readonly: function($el, state, forceSubmit) {
                this.destroy($el).init($el, { readonly: state }, forceSubmit);

                return this;
            }
        };
    }
);
