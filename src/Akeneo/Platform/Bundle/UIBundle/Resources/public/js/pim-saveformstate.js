define(
    ['jquery'],
    function ($) {
        'use strict';

        var formId;
        var cb;
        function saveFormState() {
            var $form        = $('#' + formId);
            var activeTab    = $form.find('#form-navbar').find('li.active').find('a').attr('href');
            var $activeGroup = $form.find('.tab-pane.active').find('.tab-groups').find('li.active').find('a');
            var activeGroup;

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
            if (sessionStorage.redirectTab) {
                var $redirectTab = $('a[href="' + sessionStorage.redirectTab + '"]');
                if ($redirectTab.length && !$('.loading-mask').is(':visible')) {
                    $redirectTab.tab('show');
                    if (cb) {
                        cb($redirectTab);
                    }
                    sessionStorage.removeItem('redirectTab');
                }
            } else if (sessionStorage[formId + '_activeTab']) {
                var $activeTab = $('a[href="' + sessionStorage[formId + '_activeTab'] + '"]');
                if ($activeTab.length) {
                    $activeTab.tab('show');
                    if (cb) {
                        cb($activeTab);
                    }
                }
            }

            if (sessionStorage[formId + '_activeGroup']) {
                var $activeGroup = $('a[href="' + sessionStorage[formId + '_activeGroup'] + '"]');
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

        return function (id, callback) {
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
