webpackJsonp([15],{

/***/ 207:
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************************************!*\
  !*** ./~/text-loader!./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/save-buttons.html ***!
  \*******************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<% if ((primaryButton) && !(secondaryButtons && secondaryButtons.length)) { %>\n    <button class=\"AknButton AknButton--apply AknButton--withIcon <%- primaryButton.className || '' %>\">\n        <i class=\"AknButton-icon icon-ok\"></i>\n        <%- primaryButton.label %>\n    </button>\n<% } else { %>\n    <div class=\"AknSeveralActionsButton AknSeveralActionsButton--apply AknDropdown\">\n        <button class=\"AknSeveralActionsButton-mainAction <%- primaryButton.className || '' %>\">\n            <%- primaryButton.label %>\n        </button>\n        <button class=\"AknSeveralActionsButton-caretContainer\" data-toggle=\"dropdown\">\n            <span class=\"AknCaret AknCaret--inverse\"></span>\n        </button>\n        <ul class=\"AknSeveralActionsButton-menu AknDropdown-menu AknDropdown-menu--right\">\n            <% _.each(secondaryButtons, function (btn) { %>\n                <li>\n                    <button type=\"button\" class=\"AknDropdown-menuLink <%- btn.className || '' %>\">\n                        <%- btn.label %>\n                    </button>\n                </li>\n            <% }) %>\n        </ul>\n    </div>\n<% } %>\n"

/***/ }),

/***/ 239:
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/save-buttons.js ***!
  \*************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Save buttons extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! backbone */ 2),
        __webpack_require__(/*! oro/mediator */ 5),
        __webpack_require__(/*! pim/form */ 41),
        __webpack_require__(/*! text-loader!pim/template/form/save-buttons */ 207)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, Backbone, mediator, BaseForm, template) {
        return BaseForm.extend({
            className: 'AknTitleContainer-rightButton',
            template: _.template(template),
            buttonDefaults: {
                priority: 100,
                events: {}
            },
            events: {},

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.model = new Backbone.Model({
                    buttons: []
                });

                this.on('save-buttons:add-button', this.addButton.bind(this));

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var buttons = this.model.get('buttons');
                this.$el.html(this.template({
                    primaryButton: _.first(buttons),
                    secondaryButtons: buttons.slice(1)
                }));
                this.delegateEvents();

                return this;
            },

            /**
             * Add a button to the main button
             *
             * @param {Object} options
             */
            addButton: function (options) {
                var button = _.extend({}, this.buttonDefaults, options);
                this.events = _.extend(this.events, button.events);
                var buttons = this.model.get('buttons');

                buttons.push(button);
                buttons = _.sortBy(buttons, 'priority').reverse();
                this.model.set('buttons', buttons);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ })

});