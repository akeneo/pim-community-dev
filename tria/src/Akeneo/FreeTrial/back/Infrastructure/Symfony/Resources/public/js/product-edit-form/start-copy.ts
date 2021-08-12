import {CompareTranslateButton} from "akeneo-pim-free-trial";

const StartCopy = require('pim/product-edit-form/start-copy');

class FreeTrialStartCopy extends StartCopy {
  render() {
    this.renderReact(
        CompareTranslateButton,
        {onClick: () => {this.startCopy();}},
        this.el
    );
  }

  startCopy() {
    super.getRoot().trigger('pim_enrich:form:start_compare_translate');
    super.startCopy();
  }

  stopCopy() {
    super.stopCopy();
    super.getRoot().trigger('pim_enrich:form:stop_compare_translate');
  }
}

export = FreeTrialStartCopy;
