
/**
 * Main image extension
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore';
import BaseForm from 'pim/form';
import template from 'pim/template/form/main-image';
export default BaseForm.extend({
    template: _.template(template),

            /**
             * {@inheritdoc}
             */
    initialize: function (config) {
        this.config = config.config;

        BaseForm.prototype.initialize.apply(this, arguments);
    },

            /**
             * {@inheritdoc}
             */
    render: function () {
        this.$el.empty().append(this.template({
            path: this.config.path
        }));

        return BaseForm.prototype.render.apply(this, arguments);
    }
});

