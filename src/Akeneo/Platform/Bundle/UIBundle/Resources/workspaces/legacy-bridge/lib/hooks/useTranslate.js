"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useTranslate = void 0;
var useDependenciesContext_1 = require("./useDependenciesContext");
var useTranslate = function () {
    var translate = useDependenciesContext_1.useDependenciesContext().translate;
    if (!translate) {
        throw new Error('[DependenciesContext]: Translate has not been properly initiated');
    }
    return translate;
};
exports.useTranslate = useTranslate;
//# sourceMappingURL=useTranslate.js.map