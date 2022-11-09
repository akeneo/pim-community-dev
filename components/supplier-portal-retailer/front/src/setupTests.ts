// jest-dom adds custom jest matchers for asserting on DOM nodes.
// allows you to do things like:
// expect(element).toHaveTextContent(/react/i)
// learn more: https://github.com/testing-library/jest-dom
import '@testing-library/jest-dom/extend-expect';
import 'jest-fetch-mock';
import {GlobalWithFetchMock} from 'jest-fetch-mock';

const customGlobal: GlobalWithFetchMock = global as unknown as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

jest.mock('@akeneo-pim-community/legacy-bridge/src/dependencies.ts');

beforeEach(() => {
    const intersectionObserverMock = () => ({
        observe: jest.fn(),
        unobserve: jest.fn(),
    });

    window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);
});
