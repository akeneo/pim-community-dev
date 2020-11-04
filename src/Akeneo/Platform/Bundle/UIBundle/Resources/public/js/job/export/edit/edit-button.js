'use strict';
/**
 * Redirect button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'pim/common/redirect', 'pim/fetcher-registry'], function(_, BaseRedirect, FetcherRegistry) {
  return BaseRedirect.extend({
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
