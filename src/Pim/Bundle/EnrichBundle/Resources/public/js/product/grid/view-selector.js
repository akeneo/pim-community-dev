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
        'pim/fetcher-registry',
        'pim/form-builder',
        'pim/form'
    ],
    function(
        _,
        $,
        FetcherRegistry,
        FormBuilder,
        BaseForm
    ) {
        return BaseForm.extend({
            id: 'view-selector',
            className: 'pull-right',

            /**
             * {@inheritdoc}
             */
            render() {
                FetcherRegistry.initialize().done(function () {
                    FormBuilder.buildForm('pim-grid-view-selector').then(function (form) {
                        return form.configure('product-grid').then(function () {
                            form.setElement('#view-selector').render();
                        });
                    }.bind(this));
                });
            }
        });
    }
);
