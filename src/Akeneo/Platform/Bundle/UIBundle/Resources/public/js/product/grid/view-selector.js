 /**
 * Parent extension to render the child extensions for the view selector in the product grid index
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'jquery',
        'pim/form-builder',
        'pim/form'
    ],
    function(
        _,
        $,
        FormBuilder,
        BaseForm
    ) {
        return BaseForm.extend({
            className: 'view-selector',
            config: {
                gridName: 'product-grid'
            },

            /**
             * {@inheritdoc}
             */
            initialize(options) {
                this.config = Object.assign(this.config, options.config || {});

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                FormBuilder.getFormMeta('pim-grid-view-selector')
                    .then(FormBuilder.buildForm)
                    .then(form => {
                        return form.configure(this.config.gridName).then(() => {
                            form.setElement('.view-selector').render();
                        });
                    });
            }
        });
    }
);
