'use strict';

/**
 * Base extension for menu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form'
    ],
    function (
        _,
        BaseForm
    ) {
        return BaseForm.extend({
            tagName: 'ul',
            className: 'AknMainMenu'
        });
    });
