// jest-dom adds custom jest matchers for asserting on DOM nodes.
// allows you to do things like:
// expect(element).toHaveTextContent(/react/i)
// learn more: https://github.com/testing-library/jest-dom
import React from 'react';
import '@testing-library/jest-dom/extend-expect';

beforeEach(() => {
  const intersectionObserverMock = () => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
  });

  window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);
});

jest.mock('@akeneo-pim-community/shared/lib/components/PimView', () => ({
  PimView: () => <></>,
}));

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (i18nKey: string) => i18nKey,
  useRouter: () => ({
    generate: (key: string) => key
  }),
  useNotify: () => () => {},
  useUserContext: () => ({
    get: (k: string) => {
      switch (k) {
        case 'catalogLocale':
          return 'en_US';
        case 'uiLocale':
          return 'en_US';
        default:
          throw new Error(`Unknown key ${k}`);
      }
    }
  }),
  useSecurity: () => ({
    isGranted: () => true
  })
}));
