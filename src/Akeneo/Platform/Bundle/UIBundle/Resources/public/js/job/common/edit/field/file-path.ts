const BaseField = require('pimui/js/job/common/edit/field/text');
const editionProvider = require('pim/edition');

class FilePath extends BaseField {
    render() {
        if (false === editionProvider.isCloudEdition()) {
            super.render();
        }

        return this;
    }
}

export = FilePath;
