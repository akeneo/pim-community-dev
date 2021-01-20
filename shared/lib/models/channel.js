var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
import { isLocales, denormalizeLocale } from '../models';
import { isLabelCollection } from '../models';
import { getLabel } from '../tools/i18n';
var getChannelLabel = function (channel, locale) {
    return getLabel(channel.labels, locale, channel.code);
};
var denormalizeChannel = function (channel) {
    if ('string' !== typeof channel.code) {
        throw new Error('Channel expects a string as code to be created');
    }
    if (!isLabelCollection(channel.labels)) {
        throw new Error('Channel expects a label collection as labels to be created');
    }
    if (!isLocales(channel.locales)) {
        throw new Error('Channel expects an array as locales to be created');
    }
    var locales = channel.locales.map(denormalizeLocale);
    return __assign(__assign({}, channel), { locales: locales });
};
export { getChannelLabel, denormalizeChannel };
//# sourceMappingURL=channel.js.map