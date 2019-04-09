const editionProvider = {
    isCloud: () => {
        return process.env.IS_CLOUD_EDITION
    }
};

export=editionProvider;