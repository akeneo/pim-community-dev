import PermissionFormRegistry from './PermissionFormRegistry';

jest.mock('../dependencies/require-context', () => ({
    __esModule: true,
    default: (module: string) => ({
        default: {module_that_should_be_imported: module},
    }),
}));

test('it can retrieve the list of registered providers', async () => {
    PermissionFormRegistry.setModuleConfig({
        providers: {
            d: {
                module: 'module/d',
                order: 20,
            },
            c: {
                module: 'module/c',
                order: 10,
            },
            a: {
                module: 'module/a',
            },
            b: {
                module: 'module/b',
            },
        },
    });

    const providers = await PermissionFormRegistry.all();

    expect(providers).toEqual([
        {module_that_should_be_imported: 'module/a'},
        {module_that_should_be_imported: 'module/b'},
        {module_that_should_be_imported: 'module/c'},
        {module_that_should_be_imported: 'module/d'},
    ]);
});
