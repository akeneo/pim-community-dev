define(
    ['underscore', 'backbone', 'tinymce'],
    function(_, Backbone) {
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
        Backbone.Router.prototype.on('route', function () {
            _.each(tinymce.editors, function(editor) {
                editor.destroy();
            });
        });
        return {
            init: function(id, options) {
                var settings = _.extend(_.clone(config), options, { selector: '#' + id });
                tinymce.init(settings);

                return this;
            },
            destroy: function(id) {
                if (tinymce.editors[id]) {
                    tinymce.editors[id].destroy();
                }

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
