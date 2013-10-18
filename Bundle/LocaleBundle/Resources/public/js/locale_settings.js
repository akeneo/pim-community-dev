/* global define */
define(['underscore', 'oro/locale-settings/data'],
function(_, settings) {
    'use strict';

    /**
     * Locale settings
     *
     * @export  oro/locale-settings
     * @class   oro.LocaleSettings
     */
    var localeSettings = {
        cache: {},
        settings: {
            locale_data: {
                US: {
                    phone_prefix: '1',
                    default_locale: 'en_US'
                }
            },
            default: {
                locale: 'en_US',
                country: 'US',
                timezone: null,
                format: {
                    address: {
                        en: '%name%\n%organization%\n%street%\n%CITY% %REGION% %COUNTRY% %postal_code%'
                    },
                    name: {
                        en_US: '%prefix% %first_name% %middle_name% %last_name% %suffix%'
                    }
                }
            },
            system: {
                locale: null,
                timezone: null,
                format: null
            },
            user: {
                locale: null,
                country: null,
                timezone: null,
                format: {
                    name: null
                }
            }
        },

        extendSettings: function(settings) {
            var deepExtend = function(target, source) {
                for (var prop in source) if (source.hasOwnProperty(prop)) {
                    if (_.isObject(target[prop])) {
                        target[prop] = deepExtend(target[prop], source[prop]);
                    } else {
                        target[prop] = source[prop];
                    }
                }
                return target;
            };
            this.settings = deepExtend(this.settings, settings);
        },

        getAddressFormat: function(country) {
            var cacheKey = 'format.address.' + country;
            if (!this.cache.hasOwnProperty(cacheKey)) {
                var countries = [
                    country,
                    this.settings.user.country,
                    this.settings.default.country
                ];

                var possibleFormats = [];
                for (var i = 0; i < countries.length; i++) {
                    if (countries[i]) {
                        possibleFormats.push('format.address.' + countries[i]);
                    }
                }

                this.cache[cacheKey] = this.getSettingsData(possibleFormats);
            }
            return this.cache[cacheKey];
        },

        getNameFormat: function(locale) {
            /**
             * Locale fallback order:
             * - given locale
             * - given locale LANG_TAG
             * - user locale
             * - user locale LANG_TAG
             * - system locale
             * - system locale LANG_TAG
             * - default locale
             * - default locale LANG_TAG
             */
            var cacheKey = 'format.name.' + locale;
            if (!this.cache.hasOwnProperty(cacheKey)) {
                var locales = [
                    locale,
                    this.settings.user.locale,
                    this.settings.system.locale,
                    this.settings.default.locale
                ];

                var getLocaleLang = function(locale) {
                    return locale ? locale.split('_')[0] : locale;
                };
                var possibleFormats = [];
                for (var i = 0; i < locales.length; i++) {
                    if (locales[i]) {
                        possibleFormats.push('format.name.' + locales[i]);
                        possibleFormats.push('format.name.' + getLocaleLang(locales[i]));
                    }
                }

                this.cache[cacheKey] = this.getSettingsData(possibleFormats);
            }
            return this.cache[cacheKey];
        },

        getCountryLocale: function(country) {
            return this.getLocaleData(country, 'default_locale')
                || this.settings.default.locale;
        },

        getSettingsData: function(path) {
            var fallbackOrder = ['user', 'system', 'default'];
            if (!_.isArray(path)) {
                path = [path];
            }
            path = _.uniq(path);
            var cacheKey = 'settings.' + path.join(':');
            if (!this.cache.hasOwnProperty(cacheKey)) {
                var self = this;
                this.cache[cacheKey] = (function() {
                    for (var k = 0; k < path.length; k++) {
                        var pathStr = path[k];
                        for (var i = 0; i < fallbackOrder.length; i++) {
                            var data = self.settings;
                            var section = fallbackOrder[i];
                            var pathParts = (section + '.' + pathStr).split('.');
                            var found = false;
                            do {
                                var property = pathParts.splice(0, 1)[0];
                                if (data && data.hasOwnProperty(property)) {
                                    data = data[property];
                                    found = true;
                                } else {
                                    found = false;
                                    break;
                                }
                            } while (pathParts.length);

                            if (found && null !== data) {
                                return data;
                            }
                        }
                    }
                    return null;
                })();
            }
            return this.cache[cacheKey];
        },

        getLocaleData: function(country, dataType) {
            if (this.settings.locale_data.hasOwnProperty(country)) {
                return this.settings.locale_data[country][dataType];
            }
            return null;
        }
    };

    localeSettings.extendSettings({
        locale_data: settings.locale_data,
        system: {format: settings.format}
    });

    return localeSettings;
});
