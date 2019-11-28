define(['module', 'underscore', 'translator-lib'],
function (module, _, Translator) {
    'use strict';

    var dict = {};
    var debug = false;
    var add = Translator.add;
    var get = Translator.get;
    var fromJSON = Translator.fromJSON;

    Translator.placeHolderPrefix = '{{ ';
    Translator.placeHolderSuffix = ' }}';

    /**
     * Adds a translation to Translator object and stores
     * translation id in protected dictionary
     *
     * @param {string} id
     */
    Translator.add = function (id) {
        dict[id] = 1;
        add.apply(Translator, arguments);
    };

    /**
     * Fetches translation by its id,
     * but before checks if the id was registered in dictionary
     *
     * @param {string} id
     * @returns {string}
     */
    Translator.get = function (id) {
        checkTranslation(id);

        return get.apply(Translator, arguments);
    };

    /**
     * Parses JSON data in store translations inside,
     * also turns on debug mode if in data was such directive
     *
     * @param {Object} data
     * @returns {Object} Translator
     */
    Translator.fromJSON = function (data) {
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }
        debug = data.debug || false;

        return fromJSON.call(Translator, data);
    };

    /**
     * Checks if translation for passed id exist, if it's debug mode
     * and there's no translation - output error message in console
     *
     * @param {string} id
     */
    function checkTranslation(id) {
        if (!debug) {
            return;
        }
        var domains = Translator.defaultDomains;
        var checker = function (domain) {
            return dict.hasOwnProperty(domain ? domain + ':' + id : id);
        };
        domains = _.union([undefined], _.isArray(domains) ? domains : [domains]);
        if (!_.some(domains, checker)) {
            console.error('Untranslated: %s', id);
        }
    }

    _.mixin({
        /**
         * Shortcut for Translator.get() method call,
         * Due to it's underscore mixin, it can be used inside templates
         * @returns {string}
         */
        __: _.bind(Translator.get, Translator)
    });

    /**
     * Shortcut for Translator.get() method call
     *
     * @export oro/translator
     * @returns {string}
     */
    return _.__;
});
