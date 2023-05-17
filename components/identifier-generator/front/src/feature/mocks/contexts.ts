export const mockedUserContext = {
  get: (k: string) => {
    switch (k) {
      case 'catalogLocale':
        return 'en_US';
      case 'uiLocale':
        return 'en_US';
      default:
        throw new Error(`Unknown key ${k}`);
    }
  },
};
