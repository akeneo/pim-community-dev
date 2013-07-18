(function(Translator, _, Oro) {
    var dict = {},
        add = Translator.add;

    /**
     * Store all translation ids which were added to Translator
     * @param id
     */
    Translator.add = function(id) {
        dict[id] = 1;
        add.apply(Translator, arguments);
    };

    /**
     * Checks if translation for passed id exist, if it's Oro.debug mode
     * and there's no translation - output error message in console
     * @param id
     */
    function checkTranslation(id) {
        if (!Oro.debug) {
            return;
        }
        var domains = Translator.defaultDomains,
            checker = function (domain){
                return dict.hasOwnProperty(domain ? domain + ':' + id : id);
            };
        domains = _.union([undefined], _.isArray(domains) ? domains : [domains]);
        if (!_.some(domains, checker)) {
            console.error('Translation "%s" does not exist!', id);
        }
    }

    _.mixin({
        /**
         * Shortcut for Translator.get() method call,
         * Due to it's underscore mixin, it can be used inside templates
         * @param id
         * @returns {string}
         */
        __: function(id) {
            checkTranslation(id);
            return Translator.get.apply(Translator, arguments);
        }
    });
}) (Translator, _, Oro);
