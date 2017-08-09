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
        'pim/form',
        'pim/template/category-tree'
    ],
    function(
        _,
        $,
        FormBuilder,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            // The id is being used category filter, need to remove
            id: 'tree',
            className: 'filter-item',
            urlParams: {},

            attributes: {
                'data-locale':  'en_US',
                'data-name': 'category',
                'data-type': 'tree',
                'data-relatedentity': 'product'
            },

            /**
             * @inheritDoc
             */
            configure() {
                this.listenTo(this.getRoot(), 'datagrid:getParams', (urlParams) => {
                    this.urlParams = urlParams;
                    this.render();
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                if (!this.configured) return this;

                this.$el.html(this.template());

                FormBuilder.buildForm('pim-grid-category-tree').then(function (form) {
                    return form.configure(this.urlParams).then(() => {
                        form.setElement('.filter-item').render();
                    });
                }.bind(this));
            }
        });
    }
);
