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
      this.visible = UserContext.get('display_' + this.config.fieldName);
      this.render();
      // const username = this.getFormData().username;
      // $.get(Routing.generate('pimee_workflow_rest_user_fields_visibility', { identifier: username }))
      //   .then((result: { [key:string] : boolean }) => {
      //     this.visible = result[this.config.fieldName];
      //
      //     this.render();
      //   })
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
