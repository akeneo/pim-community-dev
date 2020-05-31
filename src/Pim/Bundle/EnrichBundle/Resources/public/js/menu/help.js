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
    function(
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
            render: function() {
                this.getUrl().then(url => {
                    this.$el.empty().append(this.template({
                        helper: __('pim_menu.tab.help.helper'),
                        title: __('pim_menu.tab.help.title'),
                        url
                    }));
                });

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            getUrl() {
              return DataCollector.collect(this.analyticsUrl).then(data => {
                const {pim_version, pim_edition} = data;
                let version = `v${pim_version.split('.')[0]}`;
                let campaign = `${pim_edition}${pim_version}`;

                const url = new URL(`https://help.akeneo.com/pim/${version}/index.html`);
                url.searchParams.append('utm_source', 'akeneo-app');
                url.searchParams.append('utm_medium', 'interrogation-icon');
                url.searchParams.append('utm_campaign', campaign);

                return url.href;
              });
            }
        });
    });
