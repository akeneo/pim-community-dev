define(['general', 'underscore'], function(general, _) {

    function formatModuleName(name) {
        return name.replace('pimcommunity', 'pim')
    }

    return {
        config: function(moduleName) {
            var config = _.extend({
                defaultController: {
                    module: 'pim/controller/template'
                }
            }, general)

            if (moduleName) {
                var moduleConfig = config[formatModuleName(moduleName)];
                if (!moduleConfig) console.warn(moduleName + ' has no config set');

                return moduleConfig;
            }

            return config
        }
    }
});
