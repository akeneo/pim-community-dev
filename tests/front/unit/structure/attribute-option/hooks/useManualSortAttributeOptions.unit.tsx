import React, {PropsWithChildren} from 'react';
import {Provider} from 'react-redux';
import {act, renderHook} from '@testing-library/react-hooks';

import {useManualSortAttributeOptions} from 'akeneopimstructure/js/attribute-option/hooks/useManualSortAttributeOptions';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge/src';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';

const renderUseManualSortAttributeOptions = () => {
    return renderHook(() => useManualSortAttributeOptions(), {
        wrapper: ({children}: PropsWithChildren<any>) => (
            <DependenciesProvider>
                <Provider store={createStoreWithInitialState({attributeOptions: null})}>
                    <AttributeContextProvider attributeId={8} autoSortOptions={false}>
                        {children}
                    </AttributeContextProvider>
                </Provider>
            </DependenciesProvider>
        )
    });
};

const givenAttributeOptions = (): AttributeOption[] => {
    return [
        {
            id: 85,
            code: 'black',
            optionValues: {
                'en_US': {id:252, locale:'en_US', value:'Black'},
                'fr_FR':{id:253, locale:'fr_FR', value:'Noir'}
            }
        },
        {
            id: 86,
            code: 'blue',
            optionValues: {
                'en_US': {id:255, locale:'en_US', value:'Blue'},
                'fr_FR':{id:256, locale:'fr_FR', value:'Bleu'}
            }
        },
    ];
};

describe('useManualSortAttributeOptions', () => {
    beforeAll(() => {
        global.fetch = jest.fn();

        jest.spyOn(global, 'fetch').mockImplementation(() => {
            return Promise.resolve();
        });
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    it('should save the order of the attribute options list', () => {
        const attributeOptions = givenAttributeOptions();
        const {result} = renderUseManualSortAttributeOptions();

        act(() => {
            result.current(attributeOptions);
        });

        expect(global.fetch).toHaveBeenCalled();
    });
});
