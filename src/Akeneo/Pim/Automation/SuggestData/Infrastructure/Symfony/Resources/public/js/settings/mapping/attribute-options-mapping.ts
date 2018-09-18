import BaseForm = require('pimenrich/js/view/base');

/**
 * TODO
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeOptionsMapping extends BaseForm {
  /**
   * {@inheritdoc}
   */
  public render(): BaseForm {
    this.$el.html('foo');

    return this;
  }
}

export = AttributeOptionsMapping
