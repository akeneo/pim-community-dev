define(
    ['jquery', 'jquery.slimbox'],
    function ($) {
        'use strict';

        var maxFilenameLength = 20;
        var init = function (id) {
            var $el = $('#' + id);
            if (!$el.length) {
                return;
            }

            $el.on('change', function () {
                var $input          = $(this);
                var filename        = $input.val().split('\\').pop();
                var $zone           = $input.parent();
                var $info           = $input.siblings('.upload-info').first();
                var $filename       = $info.find('.upload-filename');
                var $removeBtn      = $input.siblings('.remove-upload');
                var $removeCheckbox = $input.siblings('input[type="checkbox"]');
                var $preview        = $info.find('.upload-preview');

                if (filename) {
                    var title = filename.length > maxFilenameLength ?
                        filename.substring(0, maxFilenameLength - 3) + '...' :
                        filename;
                    $filename.html(title);
                    $zone.removeClass('empty');
                    $preview.removeClass('empty').attr('title', filename);
                    $removeBtn.removeClass('hide');
                    $input.addClass('hide');
                    $removeCheckbox.removeAttr('checked');
                } else {
                    $filename.html($filename.attr('data-empty-title'));
                    $zone.addClass('empty');
                    $preview.addClass('empty').removeAttr('title');
                    $removeBtn.addClass('hide');
                    $input.removeAttr('disabled').removeClass('hide');
                    $removeCheckbox.prop('checked', true);
                }
            });

            $el.parent().on('click', '.remove-upload:not(.disabled)', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $el.wrap('<form>').closest('form').get(0).reset();
                $el.unwrap().trigger('change');
            });

            $el.parent().on('mouseover', '.upload-zone:not(.empty)', function () {
                $el.attr('disabled', 'disabled');
            }).on('mouseout', '.upload-zone:not(.empty)', function () {
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

        return {
            init: init
        };
    }
);
