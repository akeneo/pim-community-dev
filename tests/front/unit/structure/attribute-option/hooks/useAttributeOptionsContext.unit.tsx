import React, {PropsWithChildren} from 'react';
import {Provider} from 'react-redux';
import {renderHook} from '@testing-library/react-hooks';
import {useAttributeOptionsContext} from 'akeneopimstructure/js/attribute-option/hooks/useAttributeOptionsContext';
import {AttributeContextProvider, AttributeOptionsContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';

const renderUseAttributeOptionsContext = (useWrapper: boolean = true) => {
    const attributeOptions = givenAttributeOptions();
    const wrapper = ({children}: PropsWithChildren<any>) => (
        <DependenciesProvider>
            <Provider store={createStoreWithInitialState({attributeOptions})}>
                <AttributeContextProvider attributeId={8} autoSortOptions={false}>
                    <AttributeOptionsContextProvider>
                        {children}
                    </AttributeOptionsContextProvider>
                </AttributeContextProvider>
            </Provider>
        </DependenciesProvider>
    );

    const options = useWrapper ? {wrapper} : {};

    return renderHook(() => useAttributeOptionsContext(), options);
};

const givenAttributeOptions = (): AttributeOption[] => {
    return [
        {
            id: 85,
            code: 'black',
            optionValues: {
                'en_US': {id:252, locale:'en_US', value:'Black'},
                'fr_FR': {id:253, locale:'fr_FR', value:'Noir'}
            }
        },
        {
            id: 86,
            code: 'blue',
            optionValues: {
                'en_US': {id:254, locale:'en_US', value:'Blue'},
                'fr_FR': {id:255, locale:'fr_FR', value:'Bleu'}
            }
        }
    ];
};

describe('useAttributeOptionsContext', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        jest.restoreAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    it('should throw an error if the context is not defined', () => {
        jest.spyOn(React, 'useContext').mockImplementation(() => undefined);

        const {result} = renderUseAttributeOptionsContext(false);

        expect(result.error).not.toBeNull();
    });

    it('should return a default context if it is not properly initialized', () => {
        const {result} = renderUseAttributeOptionsContext(false);

        expect(result.current.attributeOptions).toBeNull();
        expect(result.current.selectedOption).toBeNull();
        expect(result.current.activateCreation).toBeDefined();
        expect(result.current.deactivateCreation).toBeDefined();
        expect(result.current.isEmpty).toBeDefined();
        expect(result.current.isEditing).toBeDefined();
        expect(result.current.isCreating).toBeDefined();
        expect(result.current.isLoading).toBeDefined();
        expect(result.current.save).toBeDefined();
        expect(result.current.remove).toBeDefined();
        expect(result.current.create).toBeDefined();
        expect(result.current.select).toBeDefined();
        expect(result.current.sort).toBeDefined();
        expect(result.current.initializeSelection).toBeDefined();
    });

    it('should return the editing option context', () => {
        const {result} = renderUseAttributeOptionsContext();
        const expectedAttributeOptions = givenAttributeOptions();

        expect(result.current.attributeOptions).toEqual(expectedAttributeOptions);
        expect(result.current.selectedOption).toBeNull();
        expect(result.current.activateCreation).toBeDefined();
        expect(result.current.deactivateCreation).toBeDefined();
        expect(result.current.isEmpty).toBeDefined();
        expect(result.current.isEditing).toBeDefined();
        expect(result.current.isCreating).toBeDefined();
        expect(result.current.isLoading).toBeDefined();
        expect(result.current.save).toBeDefined();
        expect(result.current.remove).toBeDefined();
        expect(result.current.create).toBeDefined();
        expect(result.current.select).toBeDefined();
        expect(result.current.sort).toBeDefined();
        expect(result.current.initializeSelection).toBeDefined();
    });
});
