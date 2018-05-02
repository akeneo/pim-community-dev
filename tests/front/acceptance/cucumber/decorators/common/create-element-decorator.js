const createElementDecorator = (config, parent) => async (key) => {
    const elementConfig = config[key];

    if (Array.isArray(parent)) parent = parent[0];

    let elements = await parent.$$(elementConfig.selector);

    const decoratedElements = [];

    elements.forEach(element => {
        decoratedElements.push(elementConfig.decorator(element, createElementDecorator));
    });

    return (elementConfig.multiple ? decoratedElements : decoratedElements[0]);
};

module.exports = createElementDecorator;
