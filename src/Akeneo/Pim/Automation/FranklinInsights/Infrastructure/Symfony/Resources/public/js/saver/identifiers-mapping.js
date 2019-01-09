/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

'use strict';

/**
 * Module to save identifiers mapping.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
define([
    'underscore',
    'pim/saver/base',
    'routing'
], (
    _,
    BaseSaver,
    Routing
) => {
    return _.extend({}, BaseSaver, {
        /**
         * {@inheritdoc}
         */
        getUrl: function () {
            return Routing.generate(__moduleConfig.url);
        }
    });
});
