const moduleRegistry = require('module-registry');

module.exports = (moduleName) => {
  return moduleRegistry(moduleName);
};
