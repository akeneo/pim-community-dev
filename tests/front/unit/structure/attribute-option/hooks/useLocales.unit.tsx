import React, {PropsWithChildren} from 'react';
import {renderHook} from '@testing-library/react-hooks';

import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge/src";
import useLocales from 'akeneopimstructure/js/attribute-option/hooks/useLocales';
import baseFetcher from 'akeneopimstructure/js/attribute-option/fetchers/baseFetcher';

jest.mock('akeneopimstructure/js/attribute-option/fetchers/baseFetcher');

const renderUseLocales = () => {
    return renderHook(() => useLocales(), {
        wrapper: ({children}: PropsWithChildren<any>) => (
            <DependenciesProvider>
                {children}
            </DependenciesProvider>
        )
    });
};

const givenLocales = () => [
    {code: "de_DE", label: "German (Germany)"},
    {code: "en_US", label: "English (United States)"},
    {code: "fr_FR", label: "French (France)"}
];

describe('renderUseLocales', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        jest.resetAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    it('should not load locales if the component is unmounted', () => {
        const loadedLocales = givenLocales();
        baseFetcher.mockResolvedValue(loadedLocales);
        const {result, wait, unmount} = renderUseLocales();

        unmount();

        wait(() => {
            expect(baseFetcher).toHaveBeenCalled();
            expect(result.current).toEqual([]);
        });
    });

    it('should load locales the first time', () => {
        const loadedLocales = givenLocales();
        baseFetcher.mockResolvedValue(loadedLocales);

        const {result, wait} = renderUseLocales();

        expect(result.current).toEqual([]);

        wait(() => {
            expect(result.current).toEqual(loadedLocales);
            expect(baseFetcher).toHaveBeenCalled();
        });
    });
});
