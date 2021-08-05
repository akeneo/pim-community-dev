'use strict';
/**
 * Redirect button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'oro/translator', 'pim/common/redirect', 'pim/fetcher-registry', 'pim/router'], function(_, __, BaseRedirect, FetcherRegistry, router) {
  return BaseRedirect.extend({
    /**
     * {@inheritdoc}
     */
    render: function () {
      this.isVisible().then(
        (isVisible) => {
          this.$el.html(
            this.template({
              label: __(this.config.label),
              buttonClass: `${this.config.buttonClass ?? 'AknButton--action'}${isVisible ? '' : ' AknButton--disabled'}`,
              title: isVisible ? '' : __(this.config.title)
            })
          );
        }
      );

      return this;
    },

    /**
     * Redirect to the route given in the config
     */
    redirect: function () {
      this.isVisible().then((isVisible) => {
        isVisible && router.redirect(this.getUrl());
      });
    },

    /**
     * {@inheritdoc}
     */
    isVisible: function() {
      return FetcherRegistry.getFetcher('permission')
        .fetchAll()
        .then(
          function(permissions) {
            var permission = _.findWhere(permissions.job_instances, {code: this.getFormData().code});

            return permission.edit;
          }.bind(this)
        );
    },
  });
});
