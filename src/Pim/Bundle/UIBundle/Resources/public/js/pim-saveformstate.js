define(
    ['jquery'],
    function ($) {
        var formId = null;
        function saveFormState() {
            var $form        = $(formId),
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
            if (sessionStorage.redirectTab) {
                var $redirectTab = $('a[href=' + sessionStorage.redirectTab + ']');
                if ($redirectTab.length && !$('.loading-mask').is(':visible')) {
                    $redirectTab.tab('show');
                    sessionStorage.removeItem('redirectTab');
                }
            } else if (sessionStorage[formId + '_activeTab']) {
                var $activeTab = $('a[href=' + sessionStorage[formId + '_activeTab'] + ']');
                if ($activeTab.length && !$('.loading-mask').is(':visible')) {
                    $activeTab.tab('show');
                }
            }

            if (sessionStorage[formId + '_activeGroup']) {
                var $activeGroup = $('a[href=' + sessionStorage[formId + '_activeGroup'] + ']');
                if ($activeGroup.length && !$('.loading-mask').is(':visible')) {
                    $activeGroup.tab('show');
                } else {
                    var $tree = $('div[data-selected-tree]');
                    if ($tree.length && !$('.loading-mask').is(':visible')) {
                        $tree.attr('data-selected-tree', sessionStorage[formId + '_activeGroup'].match(/\d/g).join(''));
                    }
                }
            }
        }

        return function(id) {
            if (typeof Storage === 'undefined') {
                return;
            }
            formId = '#' + id;

            if (!formId || !$(formId).length) {
                return;
            }

            restoreFormState();
            $(formId).find('a[data-toggle="tab"]').on('shown', saveFormState);
        };
    }
);
