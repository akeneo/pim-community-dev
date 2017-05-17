/**
 * Special view that serves as a bridge between its parent and another tree.
 * It builds a tree on-the-fly at configure time then adds it to its own children. The result is a fully functional
 * tree as if it was build "statically".
 * The goal is to build modular view trees without duplicating a bunch of conf.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'oro/translator',
    'pim/form',
    'pim/form-builder'
], function (
    $,
    _,
    Backbone,
    __,
    BaseForm,
    FormBuilder
) {
    return BaseForm.extend({
        /**
         * {@inheritdoc}
         */
        configure: function () {
            var typeSpecificFormName = this.getRoot().getAdditionalView('type-specific');

            if (undefined !== typeSpecificFormName && null !== typeSpecificFormName) {
                return FormBuilder.buildForm(typeSpecificFormName)
                    .then(function (form) {
                        this.addExtension(
                            form.code,
                            form,
                            'self',
                            100
                        );

                        return BaseForm.prototype.configure.apply(this);
                    }.bind(this))
                ;
            }

            return BaseForm.prototype.configure.apply(this);
        }
    });
});
