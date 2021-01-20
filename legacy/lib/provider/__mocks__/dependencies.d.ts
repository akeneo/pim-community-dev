declare const dependencies: {
    router: {
        generate: any;
        redirect: any;
    };
    translate: any;
    viewBuilder: {
        build: (viewName: string) => Promise<{
            remove: any;
            setElement: () => {
                render: any;
            };
        }>;
    };
    notify: any;
    user: {
        get: any;
        set: any;
    };
    security: {
        isGranted: any;
    };
    mediator: {
        trigger: any;
        on: any;
    };
};
export { dependencies };
