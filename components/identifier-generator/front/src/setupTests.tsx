// jest-dom adds custom jest matchers for asserting on DOM nodes.
// allows you to do things like:
// expect(element).toHaveTextContent(/react/i)
// learn more: https://github.com/testing-library/jest-dom
import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import { server } from './feature/mocks/server';
import {RouteParams} from "@akeneo-pim-community/shared";
import {createQueryParam} from "@akeneo-pim-community/shared/lib/microfrontend/model/queryParam";
import {mockedUserContext} from "./feature/mocks/contexts";
// Establish API mocking before all tests.
beforeAll(() => {
  server.listen();
  jest.spyOn(console, 'error').mockImplementation(() => null);
})
// Reset any request handlers that we may add during the tests,
// so they don't affect other tests.
afterEach(() => server.resetHandlers())
// Clean up after the tests are finished.
afterAll(() => server.close())


const mockedUseSecurity = jest.fn();

const router = {
  generate: (key: string, parameters?: RouteParams) => {
    if (!parameters) return key;
    const queryString = createQueryParam(parameters);
    return `${key}${queryString}`;
  },
};

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (i18nKey: string) => i18nKey,
  useRouter: () => {
    return router;
  },
  useNotify: () => () => {},
  useUserContext: () => {
    return mockedUserContext;
  },
  useSecurity: mockedUseSecurity,
}));

beforeEach(() => {
  const intersectionObserverMock = () => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
  });

  window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

  mockedUseSecurity.mockImplementation(() => ({isGranted: () => true}));
});

jest.mock('@akeneo-pim-community/shared/lib/components/PimView', () => ({
  PimView: () => <></>,
}));
