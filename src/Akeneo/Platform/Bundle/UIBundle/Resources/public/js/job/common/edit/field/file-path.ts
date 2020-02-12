const BaseField = require('./text');
const editionProvider = require('pim/edition');

class FilePath extends BaseField {
    render() {
        if (false === editionProvider.isCloudEdition()) {
            super.render();
        }

        return this;
    }
}

export default FilePath;
