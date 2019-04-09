const pimEdition = {
    isCloudEdition: () => {
        return 'cloud' === process.env.EDITION
    }
};

export=pimEdition;
