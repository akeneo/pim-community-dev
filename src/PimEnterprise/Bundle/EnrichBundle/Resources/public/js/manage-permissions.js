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
            var $changeList = $('<ul>', {id: 'permission-changes', 'class': 'permission-changes'});
            var $changeBlock = $('<div>', {'class': 'control-group hide'}).append(
                $('<label>', {'class': 'control-label', 'text': opts.changeBlockLabel})
            ).append(
                $('<div>', {'class': 'controls'}).append($changeList)
            ).appendTo($(opts.elementId));

            $(opts.elementId + ' select').on('change', function (e) {
                var permissionId = 'change-' + $(this).attr('id') + '-';
                var permissionLabel = $.trim($(this).parents('.control-group').children('label').text()).toLowerCase();

                if (!_.isUndefined(e.added)) {
                    permissionId += e.added.id;
                    if ($('#' + permissionId).length) {
                        $('#' + permissionId).remove();
                    } else {
                        $changeList.append($('<li>', {id: permissionId}).html(
                            _.__(opts.grantMessage, {'permission': permissionLabel, 'group': e.added.text})
                        ));
                    }

                    _.each(opts.update.add[e.target.id], function (selectId) {
                        var $toUpdate = $('#' + selectId);
                        if ($.inArray(e.added.id, $toUpdate.val()) === -1) {
                            var propagateEvent = $.Event('change');
                            propagateEvent.added = e.added;
                            var values = ($toUpdate.val() === null) ?
                                [e.added.id] :
                                $.merge($toUpdate.val(), e.added.id);
                            $toUpdate.val(values).trigger(propagateEvent);
                        }
                    });
                }

                if (!_.isUndefined(e.removed)) {
                    permissionId += e.removed.id;
                    if ($('#' + permissionId).length) {
                        $('#' + permissionId).remove();
                    } else {
                        $changeList.append($('<li>', {id: permissionId}).html(
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

                if ($changeList.children().size() > 0) {
                    $changeBlock.show();
                } else {
                    $changeBlock.hide();
                }
            });
        };
    }
);
