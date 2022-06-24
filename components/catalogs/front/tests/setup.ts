// Autoload the extend expect
import '@testing-library/jest-dom';

import {useEffect, useState} from 'react';
import {useTranslate, useSessionStorageState} from '@akeneo-pim-community/shared';

(useTranslate as jest.Mock).mockImplementation(() => (key: string) => key);

require('jest-fetch-mock').enableMocks();

jest.unmock('./ReactQueryWrapper');

(useSessionStorageState as jest.Mock).mockImplementation((defaultValue: any, key: string) => {
    const storageValue = sessionStorage.getItem(key) as string;
    const [value, setValue] = useState<any>(null !== storageValue ? JSON.parse(storageValue) : defaultValue);

    useEffect(() => {
        sessionStorage.setItem(key, JSON.stringify(value));
    }, [value]);

    return [value, setValue];
});

// Required by the Tab component from the DSM
window.IntersectionObserver = jest.fn().mockImplementation(() => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
}));
