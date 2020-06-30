import React, {PropsWithChildren} from 'react';
import {Provider} from 'react-redux';
import {act, renderHook} from '@testing-library/react-hooks';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import {useAttributeOptionsContextState, initialAttributeOptionsContextState} from 'akeneopimstructure/js/attribute-option/hooks/useAttributeOptionsContextState';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';

jest.mock('akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useCreateAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useDeleteAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useManualSortAttributeOptions');
jest.mock('@akeneo-pim-community/legacy-bridge/src');

import {useSaveAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption';
import {useCreateAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useCreateAttributeOption';
import {useDeleteAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useDeleteAttributeOption';
import {useManualSortAttributeOptions} from 'akeneopimstructure/js/attribute-option/hooks/useManualSortAttributeOptions';

import {useNotify} from '@akeneo-pim-community/legacy-bridge/src';
const {DependenciesProvider} = jest.requireActual('@akeneo-pim-community/legacy-bridge/src');

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
    return renderHook(() => useAttributeOptionsContextState(), {
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

const mockNotify = jest.fn();
const mockSave = jest.fn();
const mockCreate = jest.fn();
const mockDelete = jest.fn();
const mockSort = jest.fn();
const mockFetch = jest.fn().mockImplementation(() => {
    return Promise.resolve({
        status: 200,
        json: () => Promise.resolve({}),
    });
});

describe('useAttributeContextState', () => {
    beforeAll(() => {
        global.fetch = mockFetch;

        useSaveAttributeOption.mockImplementation(() => mockSave);
        useCreateAttributeOption.mockImplementation(() => mockCreate);
        useDeleteAttributeOption.mockImplementation(() => mockDelete);
        useManualSortAttributeOptions.mockImplementation(() => mockSort);
        useNotify.mockImplementation(() => mockNotify);
    });

    beforeEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        global.fetch.mockRestore();
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

            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState(attributeOptions);
            let currentAttributeOptions;

            mockSave.mockImplementation(async () => Promise.resolve(attributeOption));

            act(() => {
                result.current.save(attributeOption);
            });

            await waitForNextUpdate();

            expect(mockSave).toHaveBeenCalledWith(attributeOption);
            expect(mockSave).not.toThrow();

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

        it('should notify the update of the attribute option failed', async () => {
            const attributeOptions = givenAttributeOptions();
            const attributeOption = {
                id: 85,
                code: 'invalid_code',
                optionValues: {
                    'en_US': {id:252, locale:'en_US', value:'NEW Black'},
                    'fr_FR':{id:253, locale:'fr_FR', value:'NOUVEAU Noir'}
                }
            };

            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState(attributeOptions);
            let currentAttributeOptions;

            mockSave.mockRejectedValue('invalid_code');

            act(() => {
                result.current.save(attributeOption);
            });

            await waitForNextUpdate();

            expect(mockSave).toHaveBeenCalledWith(attributeOption);
            expect(mockNotify).toHaveBeenCalled();

            act(() => {
                currentAttributeOptions = result.current.attributeOptions;
            });

            expect(currentAttributeOptions).toEqual(attributeOptions);
        });
    });

    describe('remove', () => {
        it('should remove existing product', async () => {
            const attributeOptions = givenAttributeOptions();
            const expectedAttributeOptions = [
                {
                    id: 86,
                    code: 'blue',
                    optionValues: {
                        'en_US': {id:255, locale:'en_US', value:'Blue'},
                        'fr_FR':{id:256, locale:'fr_FR', value:'Bleu'}
                    }
                },
            ];
            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState(attributeOptions);
            mockDelete.mockImplementation(async () => Promise.resolve({}));

            act(() => {
                result.current.remove(85);
            });

            await waitForNextUpdate();

            expect(mockDelete).toHaveBeenCalled();
            expect(result.current.attributeOptions).toEqual(expectedAttributeOptions);
            expect(result.current.selectedOption).toBeNull();
        });

        it('should remove existing product and change selection', async () => {
            const attributeOptions = givenAttributeOptions();
            const expectedAttributeOptions = [
                {
                    id: 86,
                    code: 'blue',
                    optionValues: {
                        'en_US': {id:255, locale:'en_US', value:'Blue'},
                        'fr_FR':{id:256, locale:'fr_FR', value:'Bleu'}
                    }
                },
            ];
            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState(attributeOptions);
            mockDelete.mockImplementation(async () => Promise.resolve({}));

            act(() => {
                result.current.select(85);
            });

            expect(result.current.selectedOption).not.toBeNull();
            expect(result.current.selectedOption.id).toBe(85);

            act(() => {
                result.current.remove(85);
            });

            await waitForNextUpdate();

            expect(mockDelete).toHaveBeenCalled();
            expect(result.current.attributeOptions).toEqual(expectedAttributeOptions);
            expect(result.current.selectedOption).not.toBeNull();
            expect(result.current.selectedOption.id).toBe(86);
        });

        it('should notify the deletion of the attribute option failed', async () => {
            const attributeOptions = givenAttributeOptions();
            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState(attributeOptions);
            mockDelete.mockRejectedValue('invalid_code');

            act(() => {
                result.current.remove(99);
            });

            await waitForNextUpdate();

            expect(mockDelete).toHaveBeenCalledWith(99);
            expect(mockNotify).toHaveBeenCalled();
        });
    });

    describe('create', () => {
        it('should edit the new created option', async () => {
            const newOptionCode = 'new_option_code';
            const mockNewOption = {
                code: newOptionCode,
                id: 99,
                optionValues: {},
            };
            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState([]);
            let isEditing;

            mockCreate.mockImplementation(() => mockNewOption);

            act(() => {
                result.current.create(newOptionCode);
            });
            await waitForNextUpdate();

            expect(mockCreate).toHaveBeenCalledWith(newOptionCode);
            expect(result.current.selectedOption).not.toBeNull();
            expect(result.current.selectedOption).toEqual(mockNewOption);
            expect(result.current.attributeOptions).toContain(mockNewOption);

            act(() => {
                isEditing = result.current.isEditing();
            });

            expect(isEditing).toBe(true);
        });

        it('should notify the creation of the attribute option failed', async () => {
            const newOptionCode = 'new_option_code';
            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState([]);
            mockCreate.mockRejectedValue('invalid_code');

            act(() => {
                result.current.create(newOptionCode);
            });
            await waitForNextUpdate();

            expect(mockCreate).toHaveBeenCalledWith(newOptionCode);
            expect(mockNotify).toHaveBeenCalled();
        });
    });

    describe('sort',  () => {
        it('should dispatch the update og the attribute options list order', async () => {
            const attributeOptions = givenAttributeOptions();
            const reversedAttributeOptions = attributeOptions.reverse();
            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState(attributeOptions);

            act(() => {
                result.current.sort(reversedAttributeOptions);
            });
            await waitForNextUpdate();

            expect(mockSort).toHaveBeenCalledWith(reversedAttributeOptions);
            expect(result.current.attributeOptions).toEqual(reversedAttributeOptions);
        });
    });

    describe('select', () => {
        it('should reset the selection when the attribute options list is null', async () => {
            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState(null);
            let isCreating;
            let isEditing;

            act(() => {
                result.current.select(666);
            });
            await waitForNextUpdate();

            expect(result.current.selectedOption).toBeNull();

            act(() => {
                isCreating = result.current.isCreating();
            });

            expect(isCreating).toBe(false);

            act(() => {
                isEditing = result.current.isEditing();
            });

            expect(isEditing).toBe(false);
        });

        it('should reset the selection when the id is not in the attribute options list', async () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseAttributeOptionsContextState(attributeOptions);
            let isCreating;
            let isEditing;

            act(() => {
                result.current.select(666);
            });
            expect(result.current.selectedOption).toBeNull();

            act(() => {
                isCreating = result.current.isCreating();
            });

            expect(isCreating).toBe(false);

            act(() => {
                isEditing = result.current.isEditing();
            });

            expect(isEditing).toBe(false);
        });

        it('should in edition state when a valid option is selected', async () => {
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
            });

            act(() => {
                isCreating = result.current.isCreating();
            });

            act(() => {
                isEditing = result.current.isEditing();
            });

            expect(selectedOption.id).toBe(85);
            expect(selectedOption.code).toBe('black');
            expect(isCreating).toBe(false);
            expect(isEditing).toBe(true);
        });
    });

    describe('isLoading', () => {
        it('should check if the list of attribute options is loading', async () => {
            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState(null);

            expect(result.current.isLoading()).toBe(true);

            await waitForNextUpdate();

            expect(result.current.isLoading()).toBe(false);
        });
    });

    describe('isEmpty', () => {
        it('should be empty if the list contains no options', async () => {
            const {result} = renderUseAttributeOptionsContextState([]);
            let isEmpty;

            act(() => {
                isEmpty = result.current.isEmpty();
            });

            expect(isEmpty).toBe(true);
        });

        it('should not be empty if the list contains options',  async () => {
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
        it('should check if an option is editing', async () => {
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
            expect(result.current.isCreating()).toBe(true);
            expect(result.current.isEditing()).toBe(false);

            act(() => {
                result.current.deactivateCreation();
            });

            act(() => {
                isEditing = result.current.isEditing();
            });

            expect(result.current.isCreating()).toBe(false);
            expect(result.current.isEditing()).toBe(false);
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

    describe('initializeSelection', () => {
        it('should check if an option is creating', () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseAttributeOptionsContextState(attributeOptions);

            act(() => {
                result.current.initializeSelection(attributeOptions);
            });

            expect(result.current.selectedOption).toEqual({
                id: 85,
                code: 'black',
                optionValues: {
                    'en_US': {id:252, locale:'en_US', value:'Black'},
                    'fr_FR':{id:253, locale:'fr_FR', value:'Noir'}
                }
            });
        });

        it('should deactivate the selection if the attribute optionsis not defined', async () => {
            const {result, waitForNextUpdate} = renderUseAttributeOptionsContextState(null);

            act(() => {
                result.current.initializeSelection(null);
            });

            await waitForNextUpdate();

            expect(result.current.selectedOption).toBeNull();
        });

        it('should deactivate the selection if there are no attribute options', () => {
            const {result} = renderUseAttributeOptionsContextState([]);

            act(() => {
                result.current.initializeSelection([]);
            });

            expect(result.current.selectedOption).toBeNull();
        });

        it('should deactivate the selection if there are no attribute options', () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseAttributeOptionsContextState(attributeOptions);

            act(() => {
                result.current.select(85);
            });

            expect(result.current.selectedOption).not.toBeNull();

            let isEditing;
            act(() => {
                isEditing = result.current.isEditing();
            });
            expect(isEditing).toBe(true);

            act(() => {
                result.current.initializeSelection(attributeOptions.reverse());
            });

            expect(result.current.selectedOption).not.toBeNull();
            expect(result.current.selectedOption.id).toBe(85);
        });

        it('should not reinitialize the selection if is creating a new attribute option', async () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseAttributeOptionsContextState(attributeOptions);

            act(() => {
                result.current.activateCreation();
            });

            expect(result.current.selectedOption).toBeNull();

            act(() => {
                result.current.initializeSelection(attributeOptions);
            });

            expect(result.current.selectedOption).toBeNull();
        });
    });
});


describe('initialAttributeOptionsContextState', () => {
    it('should return default values', () => {
        expect(initialAttributeOptionsContextState.attributeOptions).toBeNull();
        expect(initialAttributeOptionsContextState.selectedOption).toBeNull();
        expect(initialAttributeOptionsContextState.deactivateCreation()).toBeUndefined();
        expect(initialAttributeOptionsContextState.activateCreation()).toBeUndefined();

        expect(initialAttributeOptionsContextState.isEmpty()).toBeFalsy();
        expect(initialAttributeOptionsContextState.isEditing()).toBeFalsy();
        expect(initialAttributeOptionsContextState.isCreating()).toBeFalsy();
        expect(initialAttributeOptionsContextState.isLoading()).toBeTruthy();

        expect(initialAttributeOptionsContextState.save({id: 1, code: 'test', optionValues: {}})).toEqual(Promise.resolve({}));
        expect(initialAttributeOptionsContextState.remove(0)).toEqual(Promise.resolve({}));
        expect(initialAttributeOptionsContextState.create('test')).toEqual(Promise.resolve({}));
        expect(initialAttributeOptionsContextState.sort([])).toEqual(Promise.resolve({}));
        expect(initialAttributeOptionsContextState.select(0)).toBeUndefined();
        expect(initialAttributeOptionsContextState.initializeSelection([])).toBeUndefined();
    });
});
