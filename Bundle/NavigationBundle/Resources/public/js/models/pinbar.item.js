var navigation = navigation || {};
navigation.pinbar = navigation.pinbar || {};

navigation.pinbar.Item = navigation.Item.extend({
    defaults: {
        title: '',
        url: null,
        position: null,
        type: 'pinbar',
        display_type: null,
        maximized: false,
        remove: false
    }
});
