webpackJsonp([33],{

/***/ 232:
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/label.js ***!
  \******************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Label extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! pim/form */ 41), __webpack_require__(/*! pim/user-context */ 7), __webpack_require__(/*! pim/i18n */ 18)], __WEBPACK_AMD_DEFINE_RESULT__ = function (BaseForm, UserContext, i18n) {
        return BaseForm.extend({
            tagName: 'h1',
            className: 'AknTitleContainer-title',

            /**
             * {@inheritdoc}
             */
            configure: function () {
                UserContext.off('change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.text(
                    this.getLabel()
                );

                return this;
            },

            /**
             * Provide the object label
             *
             * @return {String}
             */
            getLabel: function () {
                var data = this.getFormData();

                return i18n.getLabel(
                    data.labels,
                    UserContext.get('catalogLocale'),
                    data.code
                );
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});