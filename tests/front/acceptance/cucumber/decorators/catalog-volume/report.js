const Header = require('../common/header');

const Report = async (nodeElement, createElementDecorator) => {
    const children = {
        'Header':  {
            selector: '.AknTitleContainer',
            decorator: Header
        }
    };
    const getChildren = createElementDecorator(children, nodeElement);
    const getHeader = async () => await getChildren('Header');

    return { getHeader };
};

module.exports = Report;
