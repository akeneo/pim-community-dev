import React, {PropsWithChildren} from 'react';
import {act, renderHook} from '@testing-library/react-hooks';

import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge/src";
import {useSaveAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption';
import {AttributeContextProvider} from "akeneopimstructure/js/attribute-option/contexts";

const renderUseSaveAttributeOption = () => {
    const attributeId = 666;
    const autoSort = false;

    return renderHook(() => useSaveAttributeOption(), {
        wrapper: ({children}: PropsWithChildren<any>) => (
            <DependenciesProvider>
                <AttributeContextProvider attributeId={attributeId} autoSortOptions={autoSort}>{children}</AttributeContextProvider>
            </DependenciesProvider>
        )
    });
};

describe('useSaveAttributeOption', () => {
    beforeAll(() => {
        global.fetch = jest.fn();
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        global.fetch.mockRestore();
    });


    it('should not throw error when the save succeed', async () => {
        jest.spyOn(global, 'fetch').mockImplementation(() => {
            return Promise.resolve({
                status: 200,
            });
        });

        const attributeOption = {
            id: 999,
            code: 'valid_code',
            optionValues: {
                'en_US': {id: 1111, locale: 'en_US', value: 'value'},
                'fr_FR':{id: 1112, locale: 'fr_FR', value: 'value'}
            }
        };

        const {result} = renderUseSaveAttributeOption();
        let save;
        let response;
        let error;

        await act(async () => {
            save = result.current;
            response = await save(attributeOption);
        });

        expect(global.fetch).toHaveBeenCalled();
        expect(response).toBeUndefined();
        expect(error).toBeUndefined();
    });

    it('should throw the option code as error when the save failed on option', async () => {
        jest.spyOn(global, 'fetch').mockImplementation(() => {
            return Promise.resolve({
                status: 400,
                json: () => Promise.resolve({
                    code: 'black'
                })
            });
        });

        const attributeOption = {
            id: 999,
            code: 'invalid_code',
            optionValues: {
                'en_US': {id: 1111, locale: 'en_US', value: 'value'},
                'fr_FR':{id: 1112, locale: 'fr_FR', value: 'value'}
            }
        };

        const {result} = renderUseSaveAttributeOption();
        let save;
        let response;
        let error;

        await act(async () => {
            try {

                save = result.current;
                response = await save(attributeOption);
            } catch (e) {
                error = e;
            }
        });

        expect(global.fetch).toHaveBeenCalled();
        expect(response).toBeUndefined();
        expect(error).toBe('black');
    });

    it('should throw the option code as error when the save failed on an option value', async () => {
        jest.spyOn(global, 'fetch').mockImplementation(() => {
            return Promise.resolve({
                status: 400,
                json: () => Promise.resolve({
                    optionValues: {
                        'en_US': {id: 1111, locale: 'en_US', value: 'invalid_value'},
                        'fr_FR':{id: 1112, locale: 'fr_FR', value: 'invalid_value'}
                    }
                })
            });
        });

        const attributeOption = {
            id: 999,
            code: 'valid_code',
            optionValues: {
                'en_US': {id: 1111, locale: 'en_US', value: 'invalid_value'},
                'fr_FR':{id: 1112, locale: 'fr_FR', value: 'invalid_value'}
            }
        };

        const {result} = renderUseSaveAttributeOption();
        let save;
        let response;
        let error;

        await act(async () => {
            try {
                save = result.current;
                response = await save(attributeOption);
            } catch (e) {
                error = e;
            }
        });

        expect(global.fetch).toHaveBeenCalled();
        expect(response).toBeUndefined();
        expect(error).toEqual({
            'en_US': {id: 1111, locale: 'en_US', value: 'invalid_value'},
            'fr_FR':{id: 1112, locale: 'fr_FR', value: 'invalid_value'}
        });
    });

});
