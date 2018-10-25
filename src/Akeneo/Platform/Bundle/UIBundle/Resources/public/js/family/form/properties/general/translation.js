'use strict';

/**
 * Family label translation fields view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'pim/common/properties/translation',
    'pim/security-context'
],
function (
    BaseTranslation,
    SecurityContext
) {
    return BaseTranslation.extend({
        /**
         * {@inheritdoc}
         */
        isReadOnly: function () {
            return !SecurityContext.isGranted('pim_enrich_family_edit_properties');
        }
    });
});
