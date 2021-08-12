const BaseForm = require('pim/form');

class EmptyCreateUserButton extends BaseForm {
  render() {
    // Delete the div container to not display the separator in the data-drop-zone="buttons"
    this.$el.remove();

    return this;
  }
}

export = EmptyCreateUserButton;
