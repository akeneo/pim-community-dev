/**
 * Extension for displaying help link with version numbers
 *
 * @author    Tamara Robichet <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/data-collector',
        'pim/template/menu/help'
    ],
    function (
        _,
        __,
        BaseForm,
        DataCollector,
        template
    ) {
        return BaseForm.extend({
            analyticsUrl: 'pim_analytics_data_collect',
            className: 'AknHeader-menuItemContainer',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.getVersion().then(version => {
                    this.$el.empty().append(this.template({
                        helper: __('pim_menu.tab.help.helper'),
                        title: __('pim_menu.tab.help.title'),
                        version
                    }));
                });

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            getVersion() {
                return DataCollector.collect(this.analyticsUrl).then((data) => {
                    return data.pim_version.substring(0, 1);
                });
            }
        });
    });
