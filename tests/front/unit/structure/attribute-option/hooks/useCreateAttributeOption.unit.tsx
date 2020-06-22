import React, {PropsWithChildren} from 'react';
import {act, renderHook} from '@testing-library/react-hooks';

import {useCreateAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useCreateAttributeOption';
import {AttributeContextProvider} from "akeneopimstructure/js/attribute-option/contexts";
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge/src";

const renderUseCreateAttributeOption = () => {
    const attributeId = 666;
    const autoSort = false;

    return renderHook(() => useCreateAttributeOption(), {
        wrapper: ({children}: PropsWithChildren<any>) => (
            <DependenciesProvider>
                <AttributeContextProvider attributeId={attributeId} autoSortOptions={autoSort}>{children}</AttributeContextProvider>
            </DependenciesProvider>
        )
    });
};

const validCreatedAttributeOption = {
    id: 999,
    code: 'black',
    optionValues: {
        'en_US': {id: 1111, locale: 'en_US', value: ''},
        'fr_FR':{id: 1112, locale: 'fr_FR', value: ''}
    }
};

describe('useCreateAttributeOption', () => {
    beforeAll(() => {
        global.fetch = jest.fn();
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        global.fetch.mockRestore();
    });

    it('should return the option when the creation succeed', async () => {
        jest.spyOn(global, 'fetch').mockImplementation(() => {
            return Promise.resolve({
                status: 200,
                json: () => Promise.resolve(validCreatedAttributeOption)
            });
        });

        const {result} = renderUseCreateAttributeOption();
        let create;
        let response;

        await act(async () => {
            create = result.current;
            response = await create('valid_code');
        });

        expect(global.fetch).toHaveBeenCalled();
        expect(response).toEqual(validCreatedAttributeOption);
    });

    it('should throw the option code as error when the creation failed', async () => {
        jest.spyOn(global, 'fetch').mockImplementation(() => {
            return Promise.resolve({
                status: 400,
                json: () => Promise.resolve({
                    code: 'invalid_code'
                })
            });
        });

        const {result} = renderUseCreateAttributeOption();
        let create;
        let response;
        let error;

        await act(async () => {
            try {

                create = result.current;
                response = await create('invalid_code');
            } catch (e) {
                error = e;
            }
        });

        expect(global.fetch).toHaveBeenCalled();
        expect(response).toBeUndefined();
        expect(error).toBe('invalid_code');
    });
});
