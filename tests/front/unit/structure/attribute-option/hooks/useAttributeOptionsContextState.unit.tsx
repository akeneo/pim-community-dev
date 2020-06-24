import React, {PropsWithChildren} from 'react';
import {Provider} from 'react-redux';
import {act, renderHook} from '@testing-library/react-hooks';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import {useAttributeOptionsContextState} from 'akeneopimstructure/js/attribute-option/hooks/useAttributeOptionsContextState';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';

import {useSaveAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption';
import {useCreateAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useCreateAttributeOption';
import {useDeleteAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useDeleteAttributeOption';
import {useManualSortAttributeOptions} from 'akeneopimstructure/js/attribute-option/hooks/useManualSortAttributeOptions';
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge/src";

jest.mock('akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useCreateAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useDeleteAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useManualSortAttributeOptions');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useManualSortAttributeOptions');

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

const renderUseAttributeOptionsContextState = (attributeOptions: AttributeOption[]|null) => {
    return renderHook(() => useAttributeOptionsContextState(8), {
        wrapper: ({children}: PropsWithChildren<any>) => (
            <DependenciesProvider>
                <Provider store={createStoreWithInitialState({attributeOptions})}>
                    <AttributeContextProvider attributeId={8} autoSortOptions={false}>
                        {children}
                    </AttributeContextProvider>
                </Provider>
            </DependenciesProvider>
        )
    });
};

const mockSave = jest.fn();
const mockCreate = jest.fn();
const mockDelete = jest.fn();
const mockSort = jest.fn();

describe('useAttributeContextState', () => {
    beforeAll(() => {
        global.fetch = jest.fn();

        useSaveAttributeOption.mockImplementation(() => mockSave);
        useCreateAttributeOption.mockImplementation(() => mockCreate);
        useDeleteAttributeOption.mockImplementation(() => mockDelete);
        useManualSortAttributeOptions.mockImplementation(() => mockSort);
    });

    beforeEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    describe('save', () => {
        it('should dispatch the product has been successfully saved', async () => {
            const attributeOptions = givenAttributeOptions();
            const attributeOption = {
                id: 85,
                code: 'black',
                optionValues: {
                    'en_US': {id:252, locale:'en_US', value:'NEW Black'},
                    'fr_FR':{id:253, locale:'fr_FR', value:'NOUVEAU Noir'}
                }
            };

            const {result, wait} = renderUseAttributeOptionsContextState(attributeOptions);
            let currentAttributeOptions;

            mockSave.mockImplementation(async () => Promise.resolve(attributeOption));

            act(() => {
                result.current.save(attributeOption);
            });

            await wait(() => {
                expect(mockSave).toHaveBeenCalledWith(attributeOption);
                expect(mockSave).not.toThrow();
            });

            act(() => {
                currentAttributeOptions = result.current.attributeOptions;
            });

            expect(currentAttributeOptions).toEqual([
                {
                    id: 85,
                    code: 'black',
                    optionValues: {
                        'en_US': {id:252, locale:'en_US', value:'NEW Black'},
                        'fr_FR':{id:253, locale:'fr_FR', value:'NOUVEAU Noir'}
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
            ]);
        });

        it('should notify the update of the product has failed', async () => {
            const attributeOptions = givenAttributeOptions();
            const attributeOption = {
                id: 85,
                code: 'invalid_code',
                optionValues: {
                    'en_US': {id:252, locale:'en_US', value:'NEW Black'},
                    'fr_FR':{id:253, locale:'fr_FR', value:'NOUVEAU Noir'}
                }
            };

            const {result, wait} = renderUseAttributeOptionsContextState(attributeOptions);
            let currentAttributeOptions;

            mockSave.mockRejectedValue('invalid_code');

            act(() => {
                result.current.save(attributeOption);
            });

            await wait(() => {
                expect(mockSave).toHaveBeenCalledWith(attributeOption);
                expect(mockSave).toThrow();
            });

            act(() => {
                currentAttributeOptions = result.current.attributeOptions;
            });

            expect(result.current.attributeOptions).toEqual(attributeOptions);
        });
    });

    describe('remove', () => {});

    describe('create', () => {
        it('should edit the new created option', async () => {
            const newOptionCode = 'new_option_code';
            const mockNewOption = {
                code: newOptionCode,
                id: 99,
                optionValues: {},
            };
            const {result, wait} = renderUseAttributeOptionsContextState([]);
            let attributeOptions;
            let selectOption;
            let isEditing;

            mockCreate.mockImplementation(() => mockNewOption);

            act(() => {
                result.current.create(newOptionCode);
            });

            await wait(() => {
                expect(mockCreate).toHaveBeenCalledWith(newOptionCode);
            });

            act(() => {
                selectOption = result.current.selectedOption;
                attributeOptions = result.current.attributeOptions;
                isEditing = result.current.isEditing();
            });

            expect(selectOption).not.toBeNull();
            expect(selectOption).toEqual(mockNewOption);
            expect(isEditing).toBe(true);
            expect(attributeOptions).toContain(mockNewOption);
        });
    });

    describe('sort',  () => {
        it('should dispatch the update og the attribute options list order', async () => {
            const attributeOptions = givenAttributeOptions();
            const reversedAttributeOptions = attributeOptions.reverse();
            const {result, wait} = renderUseAttributeOptionsContextState(attributeOptions);
            let sortedAttributeOptions;

            act(() => {
                result.current.sort(reversedAttributeOptions);
            });

            await wait(() => {
                expect(mockSort).toHaveBeenCalledWith(reversedAttributeOptions);
            });

            act(() => {
                sortedAttributeOptions = result.current.attributeOptions;
            });

            expect(sortedAttributeOptions).toEqual(reversedAttributeOptions);
        });
    });

    describe('select', () => {
        it('should reset the selection when the attribute options list is null', () => {
            const {result} = renderUseAttributeOptionsContextState(null);
            let selectedOption;
            let isCreating;
            let isEditing;

            act(() => {
                result.current.select(666);
            });

            act(() => {
                selectedOption = result.current.selectedOption;
                isCreating = result.current.isCreating();
                isEditing = result.current.isEditing();
            });

            expect(selectedOption).toBeNull();
            expect(isCreating).toBe(false);
            expect(isEditing).toBe(false);
        });

        it('should reset the selection when the id is not in the attribute options list', () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseAttributeOptionsContextState(attributeOptions);
            let selectedOption;
            let isCreating;
            let isEditing;

            act(() => {
                result.current.select(666);
            });

            act(() => {
                selectedOption = result.current.selectedOption;
                isCreating = result.current.isCreating();
                isEditing = result.current.isEditing();
            });

            expect(selectedOption).toBeNull();
            expect(isCreating).toBe(false);
            expect(isEditing).toBe(false);
        });

        it('should in edition state when a valid option is selected', () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseAttributeOptionsContextState(attributeOptions);
            let selectedOption;
            let isCreating;
            let isEditing;

            act(() => {
                result.current.select(85);
            });

            act(() => {
                selectedOption = result.current.selectedOption;
                isCreating = result.current.isCreating();
                isEditing = result.current.isEditing();
            });

            expect(selectedOption.id).toBe(85);
            expect(selectedOption.code).toBe('black');
            expect(isCreating).toBe(false);
            expect(isEditing).toBe(true);
        });
    });

    describe('isLoading', () => {
        it('should check if the list of attribute options is loading', () => {
            const {result} = renderUseAttributeOptionsContextState(null);
            let isLoading;

            act(() => {
                isLoading = result.current.isLoading();
            });

            expect(isLoading).toBe(true);
        });
    });

    describe('isEmpty', () => {
        it('should not be empty check if the list of attribute options is null', () => {
            const {result} = renderUseAttributeOptionsContextState(null);
            let isEmpty;

            act(() => {
                isEmpty = result.current.isEmpty();
            });

            expect(isEmpty).toBe(false);
        });

        it('should be empty if the list contains no options', () => {
            const {result} = renderUseAttributeOptionsContextState([]);
            let isEmpty;

            act(() => {
                isEmpty = result.current.isEmpty();
            });

            expect(isEmpty).toBe(true);

            act(() => {
                result.current.activateCreation();
            });

            act(() => {
                isEmpty = result.current.isEmpty();
            });

            expect(isEmpty).toBe(false);

            act(() => {
                result.current.deactivateCreation();
            });

            act(() => {
                isEmpty = result.current.isEmpty();
            });

            expect(isEmpty).toBe(true);
        });

        it('should not be empty if the list contains options', () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseAttributeOptionsContextState(attributeOptions);
            let isEmpty;

            act(() => {
                isEmpty = result.current.isEmpty();
            });

            expect(isEmpty).toBe(false);
        });
    });

    describe('isEditing', () => {
        it('should check if an option is editing', () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseAttributeOptionsContextState(attributeOptions);
            let isEditing;

            act(() => {
                isEditing = result.current.isEditing();
            });

            expect(isEditing).toBe(false);

            act(() => {
                result.current.select(85);
            });

            act(() => {
                isEditing = result.current.isEditing();
            });

            expect(isEditing).toBe(true);

            act(() => {
                result.current.activateCreation();
            });

            act(() => {
                isEditing = result.current.isEditing();
            });

            expect(isEditing).toBe(false);
        });
    });

    describe('isCreating', () => {
        it('should check if an option is creating', () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseAttributeOptionsContextState(attributeOptions);
            let isCreating;

            act(() => {
                isCreating = result.current.isCreating();
            });

            expect(isCreating).toBe(false);

            act(() => {
                result.current.activateCreation();
            });

            act(() => {
                isCreating = result.current.isCreating();
            });

            expect(isCreating).toBe(true);

            act(() => {
                result.current.select(85);
            });

            act(() => {
                isCreating = result.current.isCreating();
            });

            expect(isCreating).toBe(false);
        });
    });
});
