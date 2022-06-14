const CreateButton = require('pim/form/common/index/create-button');
const FeatureFlags = require('pim/feature-flags');

class CreateUserButton extends CreateButton {
  public render() {
    if (FeatureFlags.isEnabled('free_trial')) {
      this.$el.remove();

      return this;
    }

    return super.render();
  }
}

export = CreateUserButton;
