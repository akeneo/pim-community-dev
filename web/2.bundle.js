webpackJsonp([2],{

/***/ 111:
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/completeness-fetcher.js ***!
  \*****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! routing */ 3), __webpack_require__(/*! pim/base-fetcher */ 16)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, Routing, BaseFetcher) {
    return BaseFetcher.extend({
        /**
         * Fetch completenesses for the given product id
         *
         * @param Integer productId
         *
         * @return Promise
         */
        fetchForProduct: function (productId, family) {
            if (!(productId in this.entityPromises)) {
                this.entityPromises[productId] = $.getJSON(
                    Routing.generate(this.options.urls.get, { id: productId })
                ).then(function (completenesses) {
                    return {completenesses: completenesses, family: family};
                });

                return this.entityPromises[productId];
            } else {
                return this.entityPromises[productId].then(function (completeness) {
                    return (family !== completeness.family) ?
                        {completenesses: {}, family: family} :
                        this.entityPromises[productId];
                }.bind(this));
            }

        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});