const _ = require('underscore');
const BaseText = require('pim/form/common/fields/text');
const template = require('pim/template/form/common/fields/password');

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PasswordField extends BaseText {
  readonly template = _.template(template);

  /**
   * {@inheritdoc}
   */
  renderInput(templateContext: any) {
    return this.template(Object.assign(templateContext, {value: ''}));
  }
}

export = PasswordField;
