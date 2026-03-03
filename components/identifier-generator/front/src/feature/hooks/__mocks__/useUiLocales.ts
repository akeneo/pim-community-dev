const useUiLocales: () => {} = () => {
  return {
    data: [
      {
        id: 42,
        code: 'en_US',
        label: 'English (United States)',
        region: 'United States',
        language: 'English',
      },
      {
        id: 69,
        code: 'fr_FR',
        label: 'French (France)',
        region: 'France',
        language: 'French',
      },
      {
        id: 96,
        code: 'de_DE',
        label: 'German (Germany)',
        region: 'Germany',
        language: 'German',
      },
    ],
    error: null,
    isSuccess: true,
  };
};

export {useUiLocales};
