const BaseField = require('pimui/js/job/common/edit/field/text');
const editionProvider = require('pim/edition');

class FilePath extends BaseField {
  render() {
    if (true === editionProvider.isCloudEdition()) {
      this.config.readOnly = true;
    }

    super.render();
  }
}

export = FilePath;
