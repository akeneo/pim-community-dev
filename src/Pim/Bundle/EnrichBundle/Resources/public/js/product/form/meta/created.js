
/**
 * Displays the created at meta information
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore';
import Created from 'pim/form/common/meta/created';
import template from 'pim/template/product/meta/created';
export default Created.extend({
    className: 'AknColumn-block',

    template: _.template(template)
});

