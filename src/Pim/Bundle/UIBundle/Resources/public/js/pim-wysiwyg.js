define(
    ['underscore', 'tinymce'],
    function(_) {
        var config = {
            plugins:     'link contextmenu preview code paste',
            statusbar:   true,
            menubar:     false,
            toolbar:     'bold italic underline strikethrough | bullist numlist | outdent indent | link | preview code',
            readonly:    false,
            contextmenu: 'undo redo link',
            setup:       function(ed) {
                ed.on('change', function() {
                    $('#' + ed.id).trigger('change');
                });
            }
        };
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
                var settings = tinymce.editors[id] ? tinymce.editors[id].settings : {};

                return this.destroy(id).init(id, settings);
            },
            readonly: function(id, state) {
                this.destroy(id).init(id, { readonly: state });

                return this;
            }
        };
    }
);
