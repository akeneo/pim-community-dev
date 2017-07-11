

/**
 * Prodct add attribute select line view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import BaseLine from 'pim/common/add-select/line'
import template from 'pim/template/product/add-select/attribute/line'
export default BaseLine.extend({
    template: _.template(template)
})

