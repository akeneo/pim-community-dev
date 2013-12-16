define(
    ['jquery', 'jquery.slimbox'],
    function($) {
        return function(id) {
            var $el = $('#' + id);
            if (!$el.length) {
                return;
            }

            $el.on('change', function () {
                var $input          = $(this),
                    filename        = $input.val().split('\\').pop(),
                    $zone           = $input.parent(),
                    $info           = $input.siblings('.upload-info').first(),
                    $filename       = $info.find('.upload-filename'),
                    $removeBtn      = $input.siblings('.remove-upload'),
                    $removeCheckbox = $input.siblings('input[type="checkbox"]'),
                    $preview        = $info.find('.upload-preview');

                if ($preview.prop('tagName').toLowerCase() !== 'i') {
                    var iconClass = $zone.hasClass('image') ? 'icon-camera-retro' : 'icon-file';
                    $preview.replaceWith($('<i>', { 'class': iconClass + ' upload-preview'}));
                    $preview = $info.find('.upload-preview');
                }

                if (filename) {
                    $filename.html(filename);
                    $zone.removeClass('empty');
                    $preview.removeClass('empty');
                    $removeBtn.removeClass('hide');
                    $input.addClass('hide');
                    $removeCheckbox.removeAttr('checked');
                } else {
                    $filename.html($filename.attr('data-empty-title'));
                    $zone.addClass('empty');
                    $preview.addClass('empty');
                    $removeBtn.addClass('hide');
                    $input.removeAttr('disabled').removeClass('hide');
                    $removeCheckbox.attr('checked', 'checked');
                }
            });

            $el.parent().on('click', '.remove-upload', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $el.wrap('<form>').closest('form').get(0).reset();
                $el.unwrap().trigger('change');
            });

            $el.parent().on('mouseover', '.upload-zone:not(.empty)', function() {
                $el.attr('disabled', 'disabled');
            }).on('mouseout', '.upload-zone:not(.empty)', function() {
                $el.removeAttr('disabled');
            });

            // Initialize slimbox
            if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
                $el.parent().find('a[rel^="slimbox"]').slimbox({
                    overlayOpacity: 0.3
                }, null, function (el) {
                    return (this === el) || ((this.rel.length > 8) && (this.rel === el.rel));
                });
            }
        };
    }
);
