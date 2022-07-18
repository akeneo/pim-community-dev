// Autoload the extend expect
import '@testing-library/jest-dom';
import {useEffect, useState} from 'react';
import {useSessionStorageState, useTranslate} from '@akeneo-pim-community/shared';
import automockDirectory from './automockDirectory';

jest.mock('@akeneo-pim-community/shared');
require('jest-fetch-mock').enableMocks();

automockDirectory(__dirname + '/../src');

(useTranslate as jest.Mock).mockImplementation(() => (key: string) => key);
(useSessionStorageState as jest.Mock).mockImplementation((defaultValue: any, key: string) => {
    const storageValue = sessionStorage.getItem(key) as string;
    const [value, setValue] = useState<any>(null !== storageValue ? JSON.parse(storageValue) : defaultValue);

    useEffect(() => {
        sessionStorage.setItem(key, JSON.stringify(value));
    }, [value]);

    return [value, setValue];
});

// to make DSM Tab usable with jest
window.IntersectionObserver = jest.fn().mockImplementation(() => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
}));
