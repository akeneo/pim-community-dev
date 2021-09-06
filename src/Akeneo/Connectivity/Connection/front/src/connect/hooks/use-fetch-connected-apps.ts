export const useFetchConnectedApps = () => {
    // @todo fetch connected apps

    return new Promise((resolve, reject) => {
        setTimeout(function () {
            resolve({
                "total": 3,
                "connected_apps": [
                    {
                        "id": 1,
                        "name": "Akeneo Connector for Magento 2",
                        "logo": "/dist/assets/6b1438560171f596c5bc8e1a7b7ca98b.svg",
                        "author": "Akeneo dream team",
                        "partner": "Akeneo",
                        "description": "string",
                        "url": "https://www.example.com/1",
                        "categories": ["ecommerce"],
                        "certified": true,
                        "activate_url": "https://www.example.com",
                        "callback_url": "https://www.example.com"
                    },
                    {
                        "id": 42,
                        "name": "Data sheet service - Akeneo meets priint",
                        "logo": "/dist/assets/b8bce95464699f2626e43722df23917a.svg",
                        "author": "Someone else",
                        "partner": "Akeneo partner",
                        "description": "string",
                        "url": "https://www.example.com/42",
                        "categories": ["print"],
                        "certified": false,
                        "activate_url": "https://www.example.com",
                        "callback_url": "https://www.example.com"
                    },
                    {
                        "id": 3,
                        "name": "PHPoney Connector",
                        "logo": "string",
                        "author": "string",
                        "partner": "string",
                        "description": "string",
                        "url": "string",
                        "categories": ["string"],
                        "certified": true,
                        "activate_url": "string",
                        "callback_url": "string"
                    }
                ]
            });
        }, 800);
    });
};
