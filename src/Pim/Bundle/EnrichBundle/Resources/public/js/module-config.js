define([], function() {
    return {
        config: function() {
            console.log('set module config');

            // @TODO - grab this from trhe requirejs yml files and delete after
            return {
                defaultController: {
                    module: 'pim/controller/template'
                }
            }
        }
    }
});
