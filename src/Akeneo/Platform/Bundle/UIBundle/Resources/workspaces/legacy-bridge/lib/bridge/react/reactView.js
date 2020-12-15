"use strict";
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
exports.ReactView = void 0;
var reactElementHelper_1 = require("./reactElementHelper");
var BaseView = require("pimui/js/view/base");
var ReactView = (function (_super) {
    __extends(ReactView, _super);
    function ReactView() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    ReactView.prototype.render = function () {
        this.$el.append(reactElementHelper_1.mountReactElementRef(this.reactElementToMount(), this.$el.get(0)));
        return BaseView.prototype.render.apply(this, arguments);
    };
    ReactView.prototype.remove = function () {
        reactElementHelper_1.unmountReactElementRef(this.$el.get(0));
        return BaseView.prototype.remove.apply(this, arguments);
    };
    return ReactView;
}(BaseView));
exports.ReactView = ReactView;
//# sourceMappingURL=reactView.js.map