import React, {PropsWithChildren} from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {useLocalesContext} from 'akeneopimstructure/js/attribute-option/hooks/useLocalesContext';
import {LocalesContext} from 'akeneopimstructure/js/attribute-option/contexts/LocalesContext';
import {Locale} from 'akeneopimstructure/js/attribute-option/model';

const renderUseLocalesContext = (locales: Locale[], useWrapper: boolean = true) => {
    const wrapper = ({children}: PropsWithChildren<any>) => (
        <LocalesContext.Provider value={locales}>
            {children}
        </LocalesContext.Provider>
    );

    const options = useWrapper ? {wrapper} : {};

    return renderHook(() => useLocalesContext(), options);
};

const givenLocales = () => [
    {code: 'de_DE', label: 'German (Germany)'},
    {code: 'en_US', label: 'English (United States)'},
    {code: 'fr_FR', label: 'French (France)'}
];

describe('useLocalesContext', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        jest.restoreAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    it('should throw an error if the locales context is not defined', () => {
        jest.spyOn(React, 'useContext').mockImplementation(() => undefined);

        const locales = givenLocales();

        const {result} = renderUseLocalesContext(locales, false);

        expect(result.error).not.toBeNull();
    });

    it('should return an empty list of locales it the context provider of locales is not set', () => {
        const locales = givenLocales();
        const {result} = renderUseLocalesContext(locales, false);

        expect(result.current).toEqual([]);
    });

    it('should return the list of locales', () => {
        const locales = givenLocales();
        const {result} = renderUseLocalesContext(locales);

        expect(result.current).toBe(locales);
    });
});
