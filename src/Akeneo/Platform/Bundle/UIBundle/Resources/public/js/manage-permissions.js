define(
    ['jquery', 'underscore'],
    function ($, _) {
        'use strict';

        var defaults = {
            elementId: null,
            changeBlockLabel: '',
            grantMessage: '',
            revokeMessage: '',
            update: {
                add: {},
                remove: {}
            }
        };

        return function (options) {
            var opts = _.extend({}, defaults, options);
            if (!opts.elementId) {
                return;
            }
            var $changeList = $('<ul>', {
                id: 'permission-changes',
                class: 'permission-changes AknList AknList--withDisc'
            });
            var $changeBlock = $('<div>', {'class': 'control-group hide'}).append(
                $('<label>', {'class': 'control-label', 'text': opts.changeBlockLabel})
            ).append(
                $('<div>', {'class': 'controls'}).append($changeList)
            ).appendTo($(opts.elementId));

            $(opts.elementId + ' select').on('change', function (e) {
                var permissionId = 'change-' + $(this).attr('id') + '-';
                var permissionLabel = $.trim(
                    $(this).closest('.AknFieldContainer')
                        .find('label:first')
                        .text()
                    ).toLowerCase();

                if (!_.isUndefined(e.added)) {
                    permissionId += e.added.id;
                    if ($('#' + permissionId).length) {
                        $('#' + permissionId).remove();
                    } else {
                        $changeList.append($('<li>', {id: permissionId, 'class': 'AknList-item'}).html(
                            _.__(opts.grantMessage, {'permission': permissionLabel, 'group': e.added.text})
                        ));
                    }

                    _.each(opts.update.add[e.target.id], function (selectId) {
                        var $toUpdate = $('#' + selectId);
                        if ($.inArray(e.added.id, $toUpdate.val()) === -1) {
                            var values = $.makeArray($toUpdate.val());
                            values.push(e.added.id);

                            var propagateEvent = $.Event('change');
                            propagateEvent.added = e.added;
                            $toUpdate.val(values).trigger(propagateEvent);
                        }
                    });
                }

                if (!_.isUndefined(e.removed)) {
                    permissionId += e.removed.id;
                    if ($('#' + permissionId).length) {
                        $('#' + permissionId).remove();
                    } else {
                        $changeList.append($('<li>', {id: permissionId, 'class': 'AknList-item'}).html(
                            _.__(opts.revokeMessage, {'permission': permissionLabel, 'group': e.removed.text})
                        ));
                    }

                    _.each(opts.update.remove[e.target.id], function (selectId) {
                        var $toUpdate = $('#' + selectId);
                        if ($.inArray(e.removed.id, $toUpdate.val()) > -1) {
                            var propagateEvent = $.Event('change');
                            propagateEvent.removed = e.removed;
                            $toUpdate.val($($toUpdate.val()).not([e.removed.id]).get()).trigger(propagateEvent);
                        }
                    });

                }

                if ($changeList.children().length > 0) {
                    $changeBlock.show();
                } else {
                    $changeBlock.hide();
                }
            });
        };
    }
);
