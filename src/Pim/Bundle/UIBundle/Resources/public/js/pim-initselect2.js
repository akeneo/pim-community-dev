define(
    ['jquery', 'underscore', 'jquery.select2'],
    function ($, _) {
        'use strict';
        return {
            init: function ($target) {
                var self = this;
                $target.find('input.select2:not(.select2-offscreen)').each(function () {
                    var $el   = $(this),
                        value = _.map(_.compact($el.val().split(',')), $.trim),
                        tags  = _.map(_.compact($el.attr('data-tags').split(',')), $.trim);
                    tags = _.union(tags, value).sort();
                    $el.select2({ tags: tags, tokenSeparators: [',', ' '] });
                });

                $target.find('select.select2:not(.select2-offscreen)').each(function () {
                    var $el    = $(this),
                        $empty = $el.children('[value=""]');
                    if ($empty.length && $empty.html()) {
                        $el.attr('data-placeholder', $empty.html());
                        $empty.html('');
                    }
                    $el.select2({ allowClear: true });
                });

                $target.find('input.pim-ajax-entity:not(.select2-offscreen)').each(function() {
                    self.initSelect.call(self, $(this));
                });
            },
            initSelect: function($select) {
                var options = {
                        multiple: false,
                        allowClear: false
                    },
                    self = this,
                    values = null;
                if ($select.attr('data-multiple')) {
                    options.multiple = true;
                }
                if (!options.multiple && !$select.attr('data-required')) {
                    options.allowClear = true;
                    options.placeholder = " ";
                }
                if ("0" === $select.attr('data-min-input-length')) {

                    options.query = function(query) {
                        if (!self.hasCachableResults($select) || null === values) {
                            $.get(
                                $select.attr('data-url'),
                                self.getAjaxParameters($select),
                                function(data) {
                                    values = self.getSelectOptions(data, options);
                                    query.callback(values);
                                }
                            );
                        } else {
                            query.callback(values);
                        }
                    };
                } else {
                    options.minimumInputLength = $select.attr('data-min-input-length');
                    options.ajax = {
                        url: $select.attr("data-url"),
                        cache: true,
                        data: function(term) {
                            return _.extend(
                                self.getAjaxParameters($select),
                                {
                                    search: term
                                }
                            );
                        },
                        results: function(data, page) {
                            return self.getSelectOptions(data, options);
                        }
                    };
                }
                options.initSelection = function(element, callback) {
                    var choices = $.parseJSON($select.attr("data-choices"));
                    callback(choices);
                };
                $select.select2(options);
            },
            getSelectOptions: function(data, options) {
                return data;
            },
            getAjaxParameters: function($select) {
                return {};
            },
            hasCachableResults: function($select) {
                return true;
            }
        };
    }
);
