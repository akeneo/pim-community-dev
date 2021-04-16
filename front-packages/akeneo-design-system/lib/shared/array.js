"use strict";
var __spreadArray = (this && this.__spreadArray) || function (to, from) {
    for (var i = 0, il = from.length, j = to.length; i < il; i++, j++)
        to[j] = from[i];
    return to;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.arrayUnique = void 0;
var arrayUnique = function (arrayWithDuplicatedItems, comparator) {
    if (undefined === comparator)
        return Array.from(new Set(arrayWithDuplicatedItems));
    return arrayWithDuplicatedItems.reduce(function (uniqueItems, current) {
        if (uniqueItems.some(function (item) { return comparator(item, current); })) {
            return uniqueItems;
        }
        return __spreadArray(__spreadArray([], uniqueItems), [current]);
    }, []);
};
exports.arrayUnique = arrayUnique;
//# sourceMappingURL=array.js.map