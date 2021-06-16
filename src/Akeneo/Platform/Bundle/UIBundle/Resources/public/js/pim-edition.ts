const pimEdition = {
  isCloudEdition: (): boolean => {
    return 'cloud' === process.env.EDITION;
  },
};

export default pimEdition;
