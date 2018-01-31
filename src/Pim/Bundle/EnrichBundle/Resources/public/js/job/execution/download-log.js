'use strict';
/**
 * Download log button
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/form/common/download-file',
        'pim/security-context'
    ],
    function (
        DownloadFile,
        SecurityContext
    ) {
        return DownloadFile.extend({
            /**
             * {@inheritdoc}
             */
            isVisible: function () {
                var formData = this.getFormData();
                if (DownloadFile.prototype.isVisible.apply(this)) {
                    if (formData.jobInstance.type === 'export') {
                        return SecurityContext.isGranted(this.config.aclIdExport);
                    } else if (formData.jobInstance.type === 'import') {
                        return SecurityContext.isGranted(this.config.aclIdImport);
                    } else {
                        return true;
                    }
                } else {
                    return false;
                }
            }
        });
    }
);
