export const useFetchConnectedApp = () => {
    // @todo fetch real connected app from backend

    return new Promise((resolve, reject) => {
        setTimeout(function () {
            resolve({
                id: '12345',
                name: 'App name',
                scopes: [
                    {
                        icon: 'catalog_structure',
                        type: 'view',
                        entities: 'catalog_structure'
                    },
                    {
                        icon: 'attribute_options',
                        type: 'view',
                        entities: 'attribute_options'
                    },
                    {
                        icon: 'categories',
                        type: 'edit',
                        entities: 'categories'
                    },
                    {
                        icon: 'channel_localization',
                        type: 'edit',
                        entities: 'channel_localization'
                    },
                    {
                        icon: 'channel_settings',
                        type: 'edit',
                        entities: 'channel_settings'
                    },
                    {
                        icon: 'association_types',
                        type: 'delete',
                        entities: 'association_types'
                    },
                    {
                        icon: 'products',
                        type: 'delete',
                        entities: 'products'
                    },
                ],
                connection_code: 'connectionCode',
                logo: 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                author: 'Author A',
                categories: ['e-commerce', 'print'],
                certified: false,
                partner: null,
            });
        }, 800);
    });
};
