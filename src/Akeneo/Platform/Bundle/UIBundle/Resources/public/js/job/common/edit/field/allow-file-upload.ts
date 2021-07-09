const BaseField = require('pimui/js/job/common/edit/field/switch');
const editionProvider = require('pim/edition');

class AllowFileUpload extends BaseField {
  render() {
    if (true === editionProvider.isCloudEdition()) {
      this.config.readOnly = true;
    }

    super.render();
  }
}

export = AllowFileUpload;
