

/**
 * Module to save job instance
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore';
import BaseSaver from 'pim/saver/base';
import Routing from 'routing';
export default _.extend({}, BaseSaver, {
            /**
             * {@inheritdoc}
             */
    getUrl: function (identifier) {
        return Routing.generate(__moduleConfig.url, {identifier: identifier});
    }
});

