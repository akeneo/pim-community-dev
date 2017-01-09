'use strict';

/**
 * Date format fetcher
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/fetcher-registry',
    'pim/job/common/edit/field/select'
], function (
    $,
    _,
    __,
    FetcherRegistry,
    SelectField
) {
    return SelectField.extend({
        /**
         * {@inherit}
         */
        configure: function () {
            return $.when(
                FetcherRegistry.getFetcher('formats').fetchAll(),
                SelectField.prototype.configure.apply(this, arguments)
            ).then(function (formats) {
                this.config.options = formats.date_formats;
            }.bind(this));
        }
    });
});
