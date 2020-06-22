import React, {PropsWithChildren} from 'react';
import {Provider} from 'react-redux';
import {act, renderHook} from '@testing-library/react-hooks';
import {dependencies, DependenciesContext, NotificationLevel} from '@akeneo-pim-community/legacy-bridge/src';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import {useAttributeOptionsContextState} from 'akeneopimstructure/js/attribute-option/hooks/useAttributeOptionsContextState';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
/*
import {useSaveAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption';

jest.mock('akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption');
jest.mock('akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption');
*/
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
const mockNotify = jest.fn();
const renderUseAttributeOptionsContextState = (attributeOptions: AttributeOption[]|null) => {
    const dependenciesState = {
        notify: mockNotify,
        router: dependencies.router,
        security: dependencies.security,
        translate: dependencies.translate,
        user: dependencies.user,
        viewBuilder: dependencies.viewBuilder,
        mediator: dependencies.mediator
    };

    return renderHook(() => useAttributeOptionsContextState(8), {
        wrapper: ({children}: PropsWithChildren<any>) => (
            <DependenciesContext.Provider value={dependenciesState}>
                <Provider store={createStoreWithInitialState({attributeOptions})}>
                    <AttributeContextProvider attributeId={8} autoSortOptions={false}>
                        {children}
                    </AttributeContextProvider>
                </Provider>
            </DependenciesContext.Provider>
        )
    });
};

describe('useAttributeContextState', () => {
    beforeAll(() => {
        global.fetch = jest.fn();
    });

    beforeEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    describe('save', () => {
        it('should dispatch the product has been successfully saved', () => {
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

            act(() => {
                result.current.save(attributeOption);
            });

            wait(() => {
                expect(result.current.attributeOptions).toEqual([
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
        });

        it('should notify the update of the product has failed', () => {
            const mockErrorOnSave = new Error('error during save');
            jest.spyOn(global, 'fetch').mockImplementation(() => {
                return Promise.resolve({
                    status: 400,
                    json: () => Promise.resolve({
                        code: 'invalid_code'
                    })
                });
            });
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

            act(() => {
                result.current.save(attributeOption);
            });

            expect(result.current.attributeOptions).toEqual(attributeOptions);
            expect(mockNotify).toHaveBeenCalledWith(NotificationLevel.ERROR, mockErrorOnSave);
        });
    });

    describe('sort', () => {});
    describe('select', () => {});
    describe('remove', () => {});
    describe('create', () => {});

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
        it('should check if an option is editing', () => {});
        it('should check if an option is creating', () => {});
    });

    describe('isEditing', () => {});

    describe('isCreating', () => {});
});
