const BaseField = require('./switch');
const editionProvider = require('pim/edition');

class AllowFileUpload extends BaseField {
    render() {
        if (editionProvider.isCloudEdition() === false) {
            super.render();
        }

        return this;
    }
}

export default AllowFileUpload;
