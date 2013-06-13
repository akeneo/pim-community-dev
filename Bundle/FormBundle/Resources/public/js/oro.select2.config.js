var OroSelect2Config = function (config, url, perPage) {
    this.config = config;
    this.url = url;
    this.perPage = perPage;
};

OroSelect2Config.prototype.getConfig = function () {
    var self = this;
    if (this.config.formatResult === undefined) {
        this.config.formatResult = this.format(this.config.result_template !== undefined ? this.config.result_template : false);
    }
    if (this.config.formatSelection === undefined) {
        this.config.formatSelection = this.format(this.config.selection_template !== undefined ? this.config.selection_template : false);
    }
    if (this.config.initSelection === undefined) {
        this.config.initSelection = this.initSelection;
    }
    if (this.config.ajax === undefined) {
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
    if (this.config.escapeMarkup === undefined) {
        this.config.escapeMarkup = function (m) { return m; };
    }
    return this.config;
};

OroSelect2Config.prototype.format = function (jsTemplate) {
    var self = this;
    return function (object, container, query) {
        if ($.isEmptyObject(object)) {
            return undefined;
        }
        var result = '';
        var highlight = function (str) {
            return self.highlightSelection(str, query);
        };
        if (object._html !== undefined) {
            result = object._html;
        } else if (jsTemplate) {
            object.highlight = highlight;
            var tpl = _.template(jsTemplate);
            result = tpl(object);
        } else {
            result = highlight(self.getTitle(object, self.config.properties));
        }
        return result;
    };
};

OroSelect2Config.prototype.initSelection = function (element, callback) {
    callback(element.data('entity'));
};

OroSelect2Config.prototype.highlightSelection = function (str, selection) {
    return str && selection && selection.term ? str.replace(new RegExp(selection.term, 'ig'), '<span class="select2-match">$&</span>') : str;
};

OroSelect2Config.prototype.getTitle = function (data, properties) {
    var title = '';
    if (data) {
        var result = [];
        for (var i = 0; i < properties.length; i++) {
            result.push(data[properties[i]]);
        }
        title = result.join(' ');
    }
    return title;
};
