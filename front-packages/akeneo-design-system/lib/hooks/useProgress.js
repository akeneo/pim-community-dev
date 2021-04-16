"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useProgress = void 0;
var react_1 = require("react");
var shared_1 = require("../shared");
var useProgress = function (steps) {
    if (0 === steps.length) {
        throw new Error('Steps array cannot be empty');
    }
    if (shared_1.arrayUnique(steps).length !== steps.length) {
        throw new Error('Steps array cannot have duplicated names');
    }
    var _a = react_1.useState(0), current = _a[0], setCurrent = _a[1];
    var isCurrent = function (step) { return steps.indexOf(step) === current; };
    var next = function () { return setCurrent(function (current) { return (current === steps.length - 1 ? current : current + 1); }); };
    var previous = function () { return setCurrent(function (current) { return (current === 0 ? current : current - 1); }); };
    return [isCurrent, next, previous];
};
exports.useProgress = useProgress;
//# sourceMappingURL=useProgress.js.map