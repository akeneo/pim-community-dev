define(
    ['tinymce'],
    function() {
        var config = {
            plugins:     'link contextmenu preview code paste',
            statusbar:   true,
            menubar:     false,
            toolbar:     'bold italic underline strikethrough | bullist numlist | outdent indent | link | preview code',
            contextmenu: 'undo redo link',
            setup:       function(ed) {
                ed.on('change', function() {
                    $('#' + ed.id).trigger('change');
                });
            }
        };
        return {
            init: function(id) {
                var settings = config;
                settings.selector = '#' + id;
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
                return this.destroy(id).init(id);
            }
        };
    }
);
