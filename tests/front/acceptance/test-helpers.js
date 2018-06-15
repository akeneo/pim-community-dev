const glob = require('glob');
const path = require('path');
const localeDecoratorFiles = glob.sync(path.resolve(__dirname, './cucumber/decorators/**/*.decorator.js'));
const createDecoratorFile = path.resolve(__dirname, './cucumber/decorators/common/create-element-decorator.js');
const decorators = {};

const requireDecorator = loadedDecorators => file => {
    const decorator = require(file);
    loadedDecorators[Object.keys(decorator)[0] || decorator.name] = decorator;
};

localeDecoratorFiles.forEach(requireDecorator(decorators));

module.exports = {
  decorators: Object.assign(decorators, {createElementDecorator: require(createDecoratorFile)}),
  tools: require(path.resolve(__dirname, './cucumber/tools.js'))
};
