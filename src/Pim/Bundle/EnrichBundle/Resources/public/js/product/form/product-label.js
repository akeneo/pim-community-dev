'use strict';
/**
 * Product label extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/form', 'pim/user-context'],
    function (BaseForm, UserContext) {
        return BaseForm.extend({
            tagName: 'span',
            className: 'product-label',
            configure: function () {
                UserContext.off('change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                var meta = this.getFormData().meta;

                if (meta && meta.label) {
                    this.$el.text(meta.label[UserContext.get('catalogLocale')]);
                }

                return this;
            }
        });
    }
);
