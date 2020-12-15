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
exports.ReactController = void 0;
var jquery_1 = require("jquery");
var reactElementHelper_1 = require("./reactElementHelper");
var BaseController = require('pim/controller/base');
var mediator = require('oro/mediator');
var ReactController = (function (_super) {
    __extends(ReactController, _super);
    function ReactController() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    ReactController.prototype.initialize = function () {
        mediator.on('route_start', this.handleRouteChange, this);
        return _super.prototype.initialize.call(this);
    };
    ReactController.prototype.renderRoute = function () {
        this.$el.append(reactElementHelper_1.mountReactElementRef(this.reactElementToMount(), this.$el.get(0)));
        return jquery_1.Deferred().resolve();
    };
    ReactController.prototype.remove = function () {
        mediator.off('route_start', this.handleRouteChange, this);
        this.$el.remove();
        return _super.prototype.remove.call(this);
    };
    ReactController.prototype.handleRouteChange = function (routeName) {
        if (true === this.routeGuardToUnmount().test(routeName)) {
            return;
        }
        reactElementHelper_1.unmountReactElementRef(this.$el.get(0));
    };
    return ReactController;
}(BaseController));
exports.ReactController = ReactController;
//# sourceMappingURL=reactController.js.map