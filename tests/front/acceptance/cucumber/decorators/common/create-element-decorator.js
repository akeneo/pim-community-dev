const createElementDecorator = (config, parent, single = true) => async (key) => {
    const elementConfig = config[key];

    if (Array.isArray(parent)) parent = parent[0];

    let element = await parent.$$(elementConfig.selector);

    if (single) element = element[0];

    if (elementConfig.decorator) {

        return elementConfig.decorator(element, createElementDecorator);
    }


};

module.exports = { createElementDecorator };
