'use strict';
/**
 * Redirect button for edit job (needed for being able to manage rights in EE)
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/common/redirect'
    ],
    function (_, BaseRedirect) {
        return BaseRedirect;
    }
);
