var dependencies = {
    router: {
        generate: jest.fn(function (route) { return route; }),
        redirect: jest.fn(function (url) { return url; }),
    },
    translate: jest.fn(function (key) { return key; }),
    viewBuilder: {
        build: function (viewName) {
            return Promise.resolve({
                remove: jest.fn(),
                setElement: function () {
                    return {
                        render: jest.fn(function () { return viewName; }),
                    };
                },
            });
        },
    },
    notify: jest.fn(function (level, message) {
        return level + " " + message;
    }),
    user: {
        get: jest.fn(function (data) {
            switch (data) {
                case 'catalogLocale':
                    return 'en_US';
                case 'uiLocale':
                    return 'en_US';
                default:
                    return data;
            }
        }),
        set: jest.fn(),
    },
    security: {
        isGranted: jest.fn(function (acl) { return acl; }),
    },
    mediator: {
        trigger: jest.fn(function (event) { return event; }),
        on: jest.fn(function (event, _callback) { return event; }),
    },
};
export { dependencies };
//# sourceMappingURL=dependencies.js.map