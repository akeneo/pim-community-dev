 /**
 * Parent extension to render the child extensions for the category tree in the product grid index
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
            // The id is being used category filter view
            id: 'tree',
            className: 'filter-item',
            attributes: {
                'data-name': 'category',
                'data-type': 'tree',
                'data-relatedentity': 'product'
            },

            /**
             * @inheritDoc
             */
            configure() {
                this.listenTo(this.getRoot(), 'datagrid:getParams', this.setupCategoryTree);
                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Render the category tree extensions when the datagrid is ready
             * @TODO - Rewrite datagrid view to remove the need for the event listeners here
             */
            setupCategoryTree(urlParams) {
                if (!urlParams) return;

                FormBuilder.buildForm('pim-grid-category-tree').then(form => {
                    return form.configure(urlParams).then(() => {
                        form.setElement('.filter-item').render();
                    });
                });
            }
        });
    }
);
