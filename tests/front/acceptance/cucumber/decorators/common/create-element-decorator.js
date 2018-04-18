const createElementDecorator = (config, parent) => async (key) => {
    // Throw an error if you don't find the key
    // 'keyname':  {
    //     selector: '.report',
    //     decorator: Report
    // }
    const elementConfig = config[key];
    const element = await parent.$(elementConfig.selector);

    if (elementConfig.decorator) {
        return elementConfig.decorator(element, createElementDecorator);
    }

    return element;
};

module.exports = createElementDecorator;
