import React, {PropsWithChildren} from 'react';
import {act, renderHook} from '@testing-library/react-hooks';

import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge/src";
import {useDeleteAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useDeleteAttributeOption';
import {AttributeContextProvider} from "akeneopimstructure/js/attribute-option/contexts";

const renderUseDeleteAttributeOption = () => {
    const attributeId = 666;
    const autoSort = false;

    return renderHook(() => useDeleteAttributeOption(), {
        wrapper: ({children}: PropsWithChildren<any>) => (
            <DependenciesProvider>
                <AttributeContextProvider attributeId={attributeId} autoSortOptions={autoSort}>{children}</AttributeContextProvider>
            </DependenciesProvider>
        )
    });
};

describe('useDeleteAttributeOption', () => {
    beforeAll(() => {
        global.fetch = jest.fn();
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        global.fetch.mockRestore();
    });


    it('should not throw error when the deletion succeed', async () => {
        jest.spyOn(global, 'fetch').mockImplementation(() => {
            return Promise.resolve({
                status: 200,
            });
        });

        const {result} = renderUseDeleteAttributeOption();
        let remove;
        let response;
        let error;

        await act(async () => {
            remove = result.current;
            response = await remove('valid_code');
        });

        expect(global.fetch).toHaveBeenCalled();
        expect(response).toBeUndefined();
        expect(error).toBeUndefined();
    });

    it('should throw the option code as error when the creation failed', async () => {
        jest.spyOn(global, 'fetch').mockImplementation(() => {
            return Promise.resolve({
                status: 400,
                json: () => Promise.resolve({
                    message: 'response error message'
                })
            });
        });

        const {result} = renderUseDeleteAttributeOption();
        let remove;
        let response;
        let error;

        await act(async () => {
            try {

                remove = result.current;
                response = await remove('invalid_code');
            } catch (e) {
                error = e;
            }
        });

        expect(global.fetch).toHaveBeenCalled();
        expect(response).toBeUndefined();
        expect(error).toBe('response error message');
    });

});
