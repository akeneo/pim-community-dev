/* global define */
define(['jquery', 'underscore'],
function($, _) {
    'use strict';

    /**
     * @export oro/select2-config
     */
    var Select2Config = function (config, attachedElementType, url, perPage, excluded) {
        this.config = config;
        this.attachedElementType = attachedElementType;
        this.url = url;
        this.perPage = perPage;
        this.excluded = excluded;
    };

    Select2Config.prototype = {
        getConfig: function () {
            var self = this;
            if (this.config.formatResult === undefined) {
                this.config.formatResult = this.format(this.config.result_template || false);
            }
            if (this.config.formatSelection === undefined) {
                this.config.formatSelection = this.format(this.config.selection_template || false);
            }
            if (this.config.initSelection === undefined && this.attachedElementType !== 'select') {
                this.config.initSelection = _.bind(this.initSelection, this);
            }

            var filterData = function(data) {
                if (self.excluded) {
                    var forRemove = [];
                    var results = data.results;
                    for (var i = 0; i < results.length; i++) {
                        if (results[i].hasOwnProperty('id') && self.excluded.indexOf(results[i].id) > -1) {
                            forRemove.push(i);
                        }
                    }
                    for (i = 0; i < forRemove.length; i++) {
                        results.splice(forRemove[i], 1);
                    }
                    data.results = results;
                }
                return data;
            };

            if (this.config.ajax === undefined && this.attachedElementType !== 'select') {
                this.config.ajax = {
                    'url': this.url,
                    'data': function (query, page) {
                        return {
                            'page': page,
                            'per_page': self.perPage,
                            'query': query
                        };
                    },
                    'results': function (data, page) {
                        return data;
                    }
                };
            }
            if (this.config.ajax !== undefined) {
                var resultsMethod = this.config.ajax.results;
                this.config.ajax.results = function(data, page) {
                    return filterData(resultsMethod(data, page));
                };
                if (this.config.ajax.quietMillis === undefined) {
                    this.config.ajax.quietMillis = 700;
                }
            }
            if (this.config.escapeMarkup === undefined) {
                this.config.escapeMarkup = function (m) { return m; };
            }
            if (this.config.dropdownAutoWidth === undefined) {
                this.config.dropdownAutoWidth = true;
            }
            return this.config;
        },

        format: function(jsTemplate) {
            var self = this;
            return function (object, container, query) {
                if ($.isEmptyObject(object)) {
                    return undefined;
                }
                var result = '', tpl,
                    highlight = function (str) {
                    return self.highlightSelection(str, query);
                };
                if (object._html !== undefined) {
                    result = object._html;
                } else if (jsTemplate) {
                    object.highlight = highlight;
                    tpl = _.template(jsTemplate);
                    result = tpl(object);
                } else {
                    result = highlight(self.getTitle(object, self.config.properties));
                }
                return result;
            };
        },

        initSelection: function(element, callback) {
            if (this.config.multiple === true) {
                callback(element.data('entities'));
            } else {
                callback(element.data('entities').pop());
            }
        },

        highlightSelection: function(str, selection) {
            return str && selection && selection.term ?
                str.replace(new RegExp(selection.term, 'ig'), '<span class="select2-match">$&</span>') : str;
        },

        getTitle: function(data, properties) {
            var title = '', result;
            if (data) {
                if (properties === undefined) {
                    if (data.text !== undefined) {
                        title = data.text;
                    }
                } else {
                    result = [];
                    _.each(properties, function(property) {
                        result.push(data[property]);
                    });
                    title = result.join(' ');
                }
            }
            return title;
        }
    };

    return Select2Config;
});
