'use strict';
/**
 * Redirect button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'pim/common/redirect', 'pim/fetcher-registry'], function(_, BaseRedirect, FetcherRegistry) {
  return BaseRedirect.extend({
    /**
     * {@inheritdoc}
     */
    isVisible: function() {
      const promise = $.Deferred();

      const fetchCanEditTailoredExport = async (jobInstanceCode) => {
        const response = await fetch(router.generate('', {jobInstanceCode}));
        const {canEditJob} = response.json();

        return canEditJob;
      }

      (() => {
        const canEditJob = await fetchCanEditTailoredExport(this.getFormData().code);

        promise.resolve(canEditJob);
      })();

      return promise.promise();

      return $.deferred.allFetcherRegistry.getFetcher('permission')
        .fetchAll()
        .then(
          (permissions) => {
            var permission = _.findWhere(permissions.job_instances, {code: this.getFormData().code});

            return permission.edit;
          }
        );
    },
  });
});
