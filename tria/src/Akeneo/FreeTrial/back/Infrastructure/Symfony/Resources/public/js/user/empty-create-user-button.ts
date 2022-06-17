const CreateButton = require('pim/form/common/index/create-button');
const FeatureFlags = require('pim/feature-flags');

class EmptyCreateUserButton extends CreateButton {
  render() {
    if (FeatureFlags.isEnabled('free_trial')) {
      this.$el.remove();

      return this;
    }

    return super.render();
  }
}

export = EmptyCreateUserButton;
