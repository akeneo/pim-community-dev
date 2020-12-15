"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useRoute = void 0;
var react_1 = require("react");
var useRouter_1 = require("./useRouter");
var useRoute = function (route, parameters) {
    var generate = useRouter_1.useRouter().generate;
    return react_1.useMemo(function () { return generate(route, parameters); }, [generate, route, parameters]);
};
exports.useRoute = useRoute;
//# sourceMappingURL=useRoute.js.map