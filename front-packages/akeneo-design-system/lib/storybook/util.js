"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.camelCaseToSentenceCase = void 0;
var camelCaseToSentenceCase = function (value) {
    var result = value.replace(/([A-Z])/g, ' $1');
    return capitalize(result.trim());
};
exports.camelCaseToSentenceCase = camelCaseToSentenceCase;
var capitalize = function (value) {
    return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
};
//# sourceMappingURL=util.js.map