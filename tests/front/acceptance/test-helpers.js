const glob = require('glob');
const path = require('path');
const decoratorFiles = glob.sync(path.resolve(__dirname, './cucumber/decorators/**/*.js'))
const decorators = {};

decoratorFiles.forEach(file => {
  const decorator = require(file);
  decorators[Object.keys(decorator)[0] || decorator.name] = decorator;
});

module.exports = {
  decorators,
  tools: require(path.resolve(__dirname, './cucumber/tools.js'))
};
