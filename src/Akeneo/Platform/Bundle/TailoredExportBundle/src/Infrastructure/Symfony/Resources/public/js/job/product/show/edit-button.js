'use strict';
/**
 * Redirect button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'oro/translator', 'pim/common/redirect', 'pim/fetcher-registry'], function(_, __, BaseRedirect, FetcherRegistry) {
  return BaseRedirect.extend({

    /**
     * {@inheritdoc}
     */
    render: function () {
      this.isVisible().then(
        function (isVisible) {
          this.$el.html(
            this.template({
              label: __(this.config.label),
              isVisible,
              buttonClass: (this.config.buttonClass || 'AknButton--action') + (isVisible ? '' : ' AknButton--disabled'),
            })
          );
        }.bind(this)
      );

      return this;
    },

    /**
     * {@inheritdoc}
     */
    isVisible: function() {
      return FetcherRegistry.getFetcher(`job-instance-${this.getFormData().type}`).fetch(this.getFormData().code)
        .then(
          (jobInstance) => {
            return jobInstance.permissions.edit_tailored_export;
          }
        );
    },
  });
});
