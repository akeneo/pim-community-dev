const Header = require('../common/header');
const Volume = require('./volume');

const Report = async (nodeElement, createElementDecorator) => {
    const children = {
        'Header':  {
            selector: '.AknTitleContainer',
            decorator: Header
        },
        'Volume': {
            selector: '.AknCatalogVolume-axis',
            decorator: Volume,
            multiple: true
        }
    };

    const getChildren = createElementDecorator(children, nodeElement);
    const getHeader = async () => await getChildren('Header');

    const getVolumeByType = async (typeName) => {
        let volumes = await Promise.all(await getChildren('Volume'));

        volumes = volumes.map(async (volume) => {
            return { type: await volume.getType(), volume };
        });

        return volumes.filter(volume => volume.type === typeName)[0];
    };

    return { getHeader, getVolumeByType };
};

module.exports = Report;
