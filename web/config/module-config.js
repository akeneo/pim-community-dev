define(['general'], function(general) {
    return {
        config: function() {
            return _.extend({
                defaultController: {
                    module: 'pim/controller/template'
                }
            }, general)
        }
    }
});
