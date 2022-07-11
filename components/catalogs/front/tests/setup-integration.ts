// Autoload the extend expect
import '@testing-library/jest-dom';
import {useEffect, useState} from 'react';
import {useSessionStorageState, useTranslate} from '@akeneo-pim-community/shared';
// import {indexify} from '../src/components/CatalogEdit/utils/indexify';
// import {mocked} from 'ts-jest/utils';
// import * as IndexifyModule from '../src/components/CatalogEdit/utils/indexify';
// import {indexify as indexifyMock} from '../src/components/CatalogEdit/utils/__mocks__/indexify';
// import requireMock = jest.requireMock;

jest.mock('@akeneo-pim-community/shared');
require('jest-fetch-mock').enableMocks();

// jest.mock('../src/components/CatalogEdit/utils/indexify');
// jest.mock('/home/tseho/Work/pim-community-dev/components/catalogs/front/src/components/CatalogEdit/utils/indexify');
// jest.spyOn(IndexifyModule, 'indexify').mockImplementation(indexifyMock);
// jest.mock('../utils/indexify');
// mocked(indexify).mockImplementation(() => ({}));

// console.log(requireMock('../src/components/CatalogEdit/utils/indexify'));

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
