"use strict";
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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = __importDefault(require("react"));
var react_dom_1 = __importDefault(require("react-dom"));
var reportWebVitals_1 = __importDefault(require("./reportWebVitals"));
var CatalogList_1 = require("./component/CatalogList");
var CatalogEdit_1 = require("./component/CatalogEdit");
var react_router_dom_1 = require("react-router-dom");
react_dom_1.default.render((0, jsx_runtime_1.jsx)(react_1.default.StrictMode, { children: (0, jsx_runtime_1.jsx)(react_router_dom_1.HashRouter, { children: (0, jsx_runtime_1.jsxs)(react_router_dom_1.Switch, { children: [(0, jsx_runtime_1.jsx)(react_router_dom_1.Route, __assign({ path: "/:id" }, { children: (0, jsx_runtime_1.jsx)(CatalogEdit_1.CatalogEdit, {}) })), (0, jsx_runtime_1.jsx)(react_router_dom_1.Route, __assign({ path: "/" }, { children: (0, jsx_runtime_1.jsx)(CatalogList_1.CatalogList, {}) }))] }) }) }), document.getElementById('root'));
// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
(0, reportWebVitals_1.default)();
