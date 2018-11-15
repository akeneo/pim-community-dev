/* jshint unused:vars */
define(
    ['jquery', 'underscore', 'pim/formatter/choices/base', 'pim/user-context', 'jquery.select2'],
    function ($, _, ChoicesFormatter, UserContext) {
        'use strict';

        return {
            resultsPerPage: 20,
            defaultOptions: {
                allowClear: false,
                formatSearching: function () {
                    return _.__('pim_common.select2.search');
                },
                formatNoMatches: function () {
                    return _.__('pim_common.select2.no_match');
                },
                formatLoadMore: function () {
                    return _.__('pim_common.select2.load_more');
                }
            },
            init: function ($target, options) {
                var self = this;

                $target.find('input.select2:not(.select2-offscreen)').each(function () {
                    var options = self.initOptions(options);

                    var $el = $(this);
                    var value = _.map(_.compact($el.val().split(',')), $.trim);
                    var tags  = _.map(_.compact($el.attr('data-tags').split(',')), $.trim);

                    $el.select2($.extend(true, options, {
                        tags: _.union(tags, value).sort(),
                        tokenSeparators: [',', ' ']
                    }));
                });

                $target.find('select.select2:not(.select2-offscreen)').each(function () {
                    var options = self.initOptions(options);

                    var $el = $(this);
                    var $empty = $el.children('[value=""]');

                    if ($empty.length && $empty.html()) {
                        $el.attr('data-placeholder', $empty.html());
                        $empty.html('');
                    }

                    $el.select2($.extend(true, options, {
                        allowClear: true
                    }));
                });

                $target.find('input.pim-ajax-entity:not(.select2-offscreen)').each(function () {
                    self.initSelect.call(self, $(this));
                });

                if ($target.hasClass('select-field')) {
                    options = self.initOptions(options);
                    $target.select2('destroy').select2(options);
                }

                return $target;
            },
            initSelect: function ($select, options) {
                options = this.initOptions(options);
                var self = this;
                var queryTimer;

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

                    window.clearTimeout(queryTimer);
                    queryTimer = window.setTimeout(function () {
                        $.ajax({
                            url: $select.attr('data-url'),
                            data: {
                                search: options.term,
                                options: {
                                    limit: self.resultsPerPage,
                                    page: page,
                                    locale: UserContext.get('catalogLocale')
                                }
                            },
                            dataType: 'json',
                            type: 'GET',
                            success: function (data) {
                                if (_.isUndefined(data.results)) {
                                    data.results = ChoicesFormatter.format(data);
                                }
                                options.callback({
                                    results: data.results,
                                    more: data.results.length === self.resultsPerPage,
                                    context: {
                                        page: page + 1
                                    }
                                });
                            }
                        });
                    }, 400);
                };
                options.initSelection = function (element, callback) {
                    var choices = $.parseJSON($select.attr('data-choices'));

                    callback(choices);
                };

                $select.select2(options);
            },
            getSelectOptions: function (data) {
                return data;
            },
            getAjaxParameters: function () {
                return {};
            },
            hasCachableResults: function () {
                return true;
            },
            matchLocalResults: function (data, term) {
                var matchingResults = _.filter(data.results, function (result) {
                    return $.fn.select2.defaults.matcher(term, result.text);
                });

                return _.extend({}, data, {results: matchingResults});
            },
            initOptions: function (options) {
                var defaultOptions = $.extend(true, {}, this.defaultOptions);

                return $.extend(true, defaultOptions, options);
            }
        };
    }
);
