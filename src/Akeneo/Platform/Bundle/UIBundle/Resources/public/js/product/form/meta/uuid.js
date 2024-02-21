'use strict';
/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'pim/form/common/meta/uuid', 'pim/template/product/meta/uuid'], function (_, Uuid, template) {
  return Uuid.extend({
    className: 'AknColumn-block',
    template: _.template(template),
  });
});
