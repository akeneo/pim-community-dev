const Header = require('../common/header');
const Volume = require('./volume');

const Report = async (nodeElement, createElementDecorator) => {
    const children = {
        'Header':  {
            selector: '.AknTitleContainer',
            decorator: Header
        },
        'Volume': {
            selector: '[data-field]',
            decorator: Volume
        }
    };
    const getChildren = createElementDecorator(children, nodeElement);
    const getHeader = async () => await getChildren('Header');

    const getVolume = async (name) => {
        const volumes = await getChildren('Volume');
        console.log(volumes, name);
        // get volume by name

        return volumes;
    }

    return { getHeader, getVolume };
};

module.exports = Report;
