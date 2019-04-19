const BaseField = require('pimui/js/job/common/edit/field/switch');
const editionProvider = require('pim/edition');

class AllowFileUpload extends BaseField {
    render() {
        if (editionProvider.isCloudEdition() === false) {
            super.render();
        }

        return this;
    }
}

export = AllowFileUpload;
