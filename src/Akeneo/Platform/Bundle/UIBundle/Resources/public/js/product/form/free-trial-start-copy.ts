import {CompareTranslateButton} from '@akeneo-pim-community/product';

const StartCopy = require('pim/product-edit-form/start-copy');

class FreeTrialStartCopy extends StartCopy {
  buttonDisabled: boolean = false;

  configure() {
    this.getRoot().on('pim_enrich:form:stop_copy', this.onStopCompareTranslate, this);
  }

  render() {
    this.renderReact(
      CompareTranslateButton,
      {
        onClick: () => {
          this.startCopy();
          this.buttonDisabled = true;
          this.render();
        },
        disabled: this.buttonDisabled,
      },
      this.el
    );
  }

  onStopCompareTranslate() {
    this.buttonDisabled = false;
    this.render();
  }
}

export = FreeTrialStartCopy;
