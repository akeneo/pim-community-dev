import _ from 'underscore';
import requireContext from 'require-context';


    /**
     * @export oro/tools
     * @name   oro.tools
     */
export default {
        /**
         * Loads dynamic list of modules and execute callback function with passed modules
         *
         * @param {Object.<string, string>} modules where keys are formal module names and values are actual
         * @param {function (Object)} callback
         */
    loadModules: function (modules, callback) {
        var arrayArguments = _.object(requirements,  arguments)
        var requirements = _.values(modules);

        require.ensure([], function() {
            _.each(modules, _.bind(function (value, key) {
                var module = requireContext(value)
                modules[key] = module
            }, arrayArguments));
            callback(modules);
        })
    }
};

