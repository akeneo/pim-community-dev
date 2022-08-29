// Autoload the extend expect
import '@testing-library/jest-dom';
import {useEffect, useState} from 'react';
import {useSessionStorageState, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import fetchMock from 'jest-fetch-mock';

jest.mock('@akeneo-pim-community/shared');
require('jest-fetch-mock').enableMocks();

(useTranslate as jest.Mock).mockImplementation(() => (key: string) => key);
const user = {
    get: jest.fn().mockReturnValue('en_US'),
    set: jest.fn(),
};
(useUserContext as jest.Mock).mockImplementation(() => user);
(useSessionStorageState as jest.Mock).mockImplementation((defaultValue: any, key: string) => {
    const storageValue = sessionStorage.getItem(key) as string;
    const [value, setValue] = useState<any>(null !== storageValue ? JSON.parse(storageValue) : defaultValue);

    useEffect(() => {
        sessionStorage.setItem(key, JSON.stringify(value));
    }, [value]);

    return [value, setValue];
});
(useUserContext as jest.Mock).mockImplementation(() => ({
    get: (key: string) => key === 'catalogLocale' ? 'en_US' : null,
}));

// to make DSM Tab usable with jest
window.IntersectionObserver = jest.fn().mockImplementation(() => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
}));

beforeEach(() => {
    fetchMock.resetMocks();
});
