"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useCatalogForm = exports.useCatalog = exports.CatalogEdit = exports.CatalogList = void 0;
var CatalogList = function () { return null; };
exports.CatalogList = CatalogList;
var CatalogEdit = function () { return null; };
exports.CatalogEdit = CatalogEdit;
var useCatalog = function () { return ({
    isLoading: false,
    isError: true,
    data: undefined,
    error: null,
}); };
exports.useCatalog = useCatalog;
var useCatalogForm = function () { return [undefined, function () { return Promise.reject(); }, false]; };
exports.useCatalogForm = useCatalogForm;
//# sourceMappingURL=exports.js.map