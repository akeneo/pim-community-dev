'use strict';
/**
 * Launch button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'oro/translator', 'pimcommunity/job/common/edit/launch', 'pim/fetcher-registry'], function(
  _,
  __,
  BaseLaunch,
  FetcherRegistry
) {
  return BaseLaunch.extend({
    /**
     * {@inheritdoc}
     */
    render: function () {
      this.isVisible().then(
        (isVisible) => {
          this.$el.html(
            this.template({
              label: __(this.config.label),
              buttonClass: (isVisible ? '' : ' AknButton--disabled') ,
              title: __(this.config.title)
            })
          );
        }
      );

      this.delegateEvents();

      return this;
    },

    launch: function () {
      this.isVisible().then((isVisible) => {
        isVisible && BaseLaunch.prototype.launch();
      });
    },


    /**
     * {@inheritdoc}
     */
    isVisible: function() {
      return FetcherRegistry.getFetcher('permission')
        .fetchAll()
        .then(
          (permissions) => {
            var permission = _.findWhere(permissions.job_instances, {code: this.getFormData().code});

            return permission.execute;
          }
        );
    },
  });
});
