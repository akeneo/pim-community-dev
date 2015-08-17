/* jshint unused:vars */
define(
    ['jquery', 'underscore', 'jquery.select2'],
    function ($, _) {
        'use strict';
        return {
            cacheDataSource: [],
            resultsPerPage: 20,
            init: function ($target) {
                var self = this;
                $target.find('input.select2:not(.select2-offscreen)').each(function () {
                    var $el   = $(this);
                    var value = _.map(_.compact($el.val().split(',')), $.trim);
                    var tags  = _.map(_.compact($el.attr('data-tags').split(',')), $.trim);
                    tags = _.union(tags, value).sort();
                    $el.select2({ tags: tags, tokenSeparators: [',', ' '] });
                });

                $target.find('select.select2:not(.select2-offscreen)').each(function () {
                    var $el    = $(this);
                    var $empty = $el.children('[value=""]');
                    if ($empty.length && $empty.html()) {
                        $el.attr('data-placeholder', $empty.html());
                        $empty.html('');
                    }
                    $el.select2({ allowClear: true });
                });

                $target.find('input.pim-ajax-entity:not(.select2-offscreen)').each(function () {
                    self.initSelect.call(self, $(this));
                });
            },
            initSelect: function ($select) {
                var selectId = $select.context.id;
                var options = {
                    multiple: false,
                    allowClear: false
                };
                var self = this;
                if ($select.attr('data-multiple')) {
                    options.multiple = true;
                }
                if (!options.multiple) {
                    if (!$select.attr('data-required')) {
                        options.allowClear = true;
                    }
                    options.placeholder = ' ';
                }
                options.minimumInputLength = $select.attr('data-min-input-length');
                options.query = function (options) {

                    var page = 1;

                    if (options.context && options.context.page) {
                        page = options.context.page;
                    }

                    var key = [options.term, page, selectId].join('_');
                    var cachedData = self.cacheDataSource[key];

                    if (cachedData) {
                        options.callback({
                            results: cachedData.results,
                            more: cachedData.results.length === self.resultsPerPage,
                            context: {
                                page: page + 1
                            }
                        });
                    } else {
                        $.ajax({
                            url: $select.attr('data-url'),
                            data: {
                                search: options.term,
                                options: {
                                    limit: self.resultsPerPage,
                                    page: page
                                }
                            },
                            dataType: 'json',
                            type: 'GET',
                            success: function (data) {
                                self.cacheDataSource[key] = data;

                                options.callback({
                                    results: data.results,
                                    more: data.results.length === self.resultsPerPage,
                                    context: {
                                        page: page + 1
                                    }
                                });
                            }
                        });
                    }
                };
                options.initSelection = function (element, callback) {
                    var choices = $.parseJSON($select.attr('data-choices'));
                    callback(choices);
                };
                $select.select2(options);
            },
            getSelectOptions: function (data, options) {
                return data;
            },
            getAjaxParameters: function ($select) {
                return {};
            },
            hasCachableResults: function ($select) {
                return true;
            },
            matchLocalResults: function (data, term) {
                var matchingResults = _.filter(data.results, function (result) {
                    return $.fn.select2.defaults.matcher(term, result.text);
                });

                return _.extend({}, data, { results: matchingResults });
            }
        };
    }
);
