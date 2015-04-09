define(['oro/navigation/model'],
function (NavigationModel) {
    'use strict';

    /**
     * @export  oro/navigation/pinbar/model
     * @class   oro.navigation.pinbar.Model
     * @extends oro.navigation.Model
     */
    return NavigationModel.extend({
        defaults: {
            title: '',
            url: null,
            position: null,
            type: 'pinbar',
            displayType: null,
            maximized: false,
            remove: false
        }
    });
});
