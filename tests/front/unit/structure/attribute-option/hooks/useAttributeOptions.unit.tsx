import React, {PropsWithChildren} from 'react';
import {renderHook} from '@testing-library/react-hooks';

import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge/src";
import useAttributeOptions from 'akeneopimstructure/js/attribute-option/hooks/useAttributeOptions';
import baseFetcher from 'akeneopimstructure/js/attribute-option/fetchers/baseFetcher';
import {Provider} from "react-redux";
import {createStoreWithInitialState} from "akeneopimstructure/js/attribute-option/store/store";
import {AttributeContextProvider} from "akeneopimstructure/js/attribute-option/contexts";
import {AttributeOption} from "akeneopimstructure/js/attribute-option/model";

jest.mock('akeneopimstructure/js/attribute-option/fetchers/baseFetcher');

const renderUseAttributeOptions = () => {
    return renderHook(() => useAttributeOptions(), {
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

const givenAttributeOptions = (): AttributeOption[]|null => {
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

describe('renderUseAttributeOptions', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        jest.resetAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    it('should not load options if the component is unmounted', () => {
        const loadedOptions = givenAttributeOptions();
        baseFetcher.mockResolvedValue(loadedOptions);
        const {result, wait, unmount} = renderUseAttributeOptions();

        unmount();

        wait(() => {
            expect(baseFetcher).toHaveBeenCalled();
            expect(result.current).toEqual(null);
        });
    });

    it('should load locales the first time', () => {
        const loadedOptions = givenAttributeOptions();
        baseFetcher.mockResolvedValue(loadedOptions);

        const {result, wait} = renderUseAttributeOptions();

        expect(result.current).toEqual(null);

        wait(() => {
            expect(baseFetcher).toHaveBeenCalled();
            expect(result.current).toEqual(loadedOptions);
        });
    });
});
