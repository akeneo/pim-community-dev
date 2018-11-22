const BaseBoolean = require('pim/form/common/fields/boolean');
const UserContext = require('pim/user-context');

/**
 * This module will display or not a boolean field regarding the URL called.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class VisibleBoolean extends BaseBoolean {
  private visible: (boolean|null) = null;

  /**
   * {@inheritdoc}
   */
  render() {
    if (this.visible === null) {
      this.visible = UserContext.get(this.config.visibilityField);
      this.render();
    } else {
      BaseBoolean.prototype.render.apply(this, arguments);
    }
  }

  /**
   * {@inheritdoc}
   */
  isVisible(): boolean {
    return this.visible === true;
  }
}

export = VisibleBoolean
