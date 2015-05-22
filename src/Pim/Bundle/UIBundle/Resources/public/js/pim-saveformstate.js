define(
    ['jquery'],
    function ($) {
        'use strict';

        var formId, cb;
        function saveFormState() {
            var $form        = $('#' + formId),
                activeTab    = $form.find('#form-navbar').find('li.active').find('a').attr('href'),
                $activeGroup = $form.find('.tab-pane.active').find('.tab-groups').find('li.active').find('a'),
                activeGroup;

            if ($activeGroup.length) {
                activeGroup = $activeGroup.attr('href');
                if (!activeGroup || activeGroup === '#' || activeGroup.indexOf('javascript') === 0) {
                    activeGroup = $activeGroup.attr('id') ? '#' + $activeGroup.attr('id') : null;
                }
            } else {
                activeGroup = null;
            }

            if (activeTab) {
                sessionStorage[formId + '_activeTab'] = activeTab;
            }
            if (activeGroup) {
                sessionStorage[formId + '_activeGroup'] = activeGroup;
            }
        }

        function restoreFormState() {
            if (!$('.hash-loading-mask .loading-mask').is(':visible')) {
                var $redirectTab;
                if (sessionStorage.redirectTab) {
                    $redirectTab = $('a[href=' + sessionStorage.redirectTab + ']');
                }
                if(!($redirectTab && $redirectTab.length) && sessionStorage[formId + '_activeTab']) {
                    $redirectTab = $('a[href=' + sessionStorage[formId + '_activeTab'] + ']');
                }
                if ($redirectTab && $redirectTab.length) {
                    $redirectTab.tab('show');
                    if (cb) {
                        cb($redirectTab);
                    }
                    sessionStorage.removeItem('redirectTab');
                }
            }

            if (sessionStorage[formId + '_activeGroup']) {
                var $activeGroup = $('a[href=' + sessionStorage[formId + '_activeGroup'] + ']');
                if ($activeGroup.length && !$('.loading-mask').is(':visible')) {
                    $activeGroup.tab('show');
                    if (cb) {
                        cb($activeGroup);
                    }
                } else {
                    var $tree = $('div[data-selected-tree]');
                    if ($tree.length && !$('.loading-mask').is(':visible')) {
                        $tree.attr('data-selected-tree', sessionStorage[formId + '_activeGroup'].match(/\d/g).join(''));
                    }
                }
            }
        }

        return function(id, callback) {
            if (typeof Storage === 'undefined') {
                return;
            }
            if (!id || !$('#' + id).length) {
                return;
            }
            formId = id;
            cb     = callback;

            restoreFormState();
            $('#' + formId).on('shown', 'a[data-toggle="tab"]', saveFormState);
            $('#' + formId).on('tab.loaded', restoreFormState);
        };
    }
);
