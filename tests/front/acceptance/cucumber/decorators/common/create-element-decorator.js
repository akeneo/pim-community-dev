const createElementDecorator = config => async (parent, key) => {
  const elementConfig = config[key];

  if (Array.isArray(parent)) parent = parent[0];

  if (undefined !== parent.waitFor) {
    await parent.waitFor(elementConfig.selector);
  }
  let elements = await parent.$$(elementConfig.selector);

  const decoratedElements = [];

  elements.forEach(element => {
    decoratedElements.push(elementConfig.decorator(element, createElementDecorator, parent));
  });

  return elementConfig.multiple ? decoratedElements : decoratedElements[0];
};

module.exports = createElementDecorator;
