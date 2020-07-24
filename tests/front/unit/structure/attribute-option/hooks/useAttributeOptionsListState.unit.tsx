import React, {PropsWithChildren} from 'react';
import {Provider} from 'react-redux';
import {act, renderHook} from '@testing-library/react-hooks';

import {AttributeOptionsState, createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import {
    ATTRIBUTE_OPTIONS_LIST_LOADED,
    AttributeOptionsListState,
    AttributeOptionsListStateEvent,
    useAttributeOptionsListState
} from 'akeneopimstructure/js/attribute-option/hooks/useAttributeOptionsListState';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';

const renderUseAttributeOptionsListStateHook = (initialState: AttributeOptionsState) => {
    const store = createStoreWithInitialState(initialState);

    return renderHook(() => useAttributeOptionsListState(), {
        wrapper: ({children}: PropsWithChildren<any>) => (
            <Provider store={store}>{children}</Provider>
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

const expectAttributeOptionsListLoadedEvent = (state: AttributeOptionsListState): CustomEvent<AttributeOptionsListStateEvent> => {
    const {attributeOptions, getAttributeOption, addExtraData, removeExtraData} = state;

    return new CustomEvent<AttributeOptionsListStateEvent>(ATTRIBUTE_OPTIONS_LIST_LOADED, {
        detail: {
            attributeOptions,
            getAttributeOption,
            addExtraData,
            removeExtraData
        }
    });
};

describe('useAttributeOptionsList', () => {
    beforeAll(() => {
        jest.spyOn(window, 'dispatchEvent').mockImplementation(() => true);
    });

    afterAll(() => {
        window.dispatchEvent.mockRestore();
    });

    describe('dispatch attribute options list ist loaded', () => {
        it('it should not dispatch the attribute options list when it is null', () => {
            const {result} = renderUseAttributeOptionsListStateHook({
                attributeOptions: null,
            });

            act(() => {
                const expectedEvent = expectAttributeOptionsListLoadedEvent(result.current);
                expect(window.dispatchEvent).not.toHaveBeenCalledWith(expectedEvent);
            });
        });

        it('it should dispatch the attribute options list when it is loaded', () => {
            const {result} = renderUseAttributeOptionsListStateHook({
                attributeOptions: givenAttributeOptions(),
            });

            act(() => {
                const expectedEvent = expectAttributeOptionsListLoadedEvent(result.current);
                expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
            });
        });

        it('it should dispatch the attribute options list when it is loaded but empty', () => {
            const {result} = renderUseAttributeOptionsListStateHook({
                attributeOptions: [],
            });

            act(() => {
                const expectedEvent = expectAttributeOptionsListLoadedEvent(result.current);
                expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
            });
        });
    });

    describe('getAttributeOption', () => {
        it('it should retrieve the attribute option with a valid code', () => {
            const {result} = renderUseAttributeOptionsListStateHook({
                attributeOptions: givenAttributeOptions(),
            });
            let blue;
            let red;

            act(() => {
                blue = result.current.getAttributeOption('blue');
                red = result.current.getAttributeOption('red');
            });

            expect(blue).toEqual({
                id: 86,
                code: 'blue',
                optionValues: {
                    'en_US': {id:255, locale:'en_US', value:'Blue'},
                    'fr_FR':{id:256, locale:'fr_FR', value:'Bleu'}
                }
            });
            expect(red).toBeUndefined();
        });

        it('it should return undefined when the attribute options list is null', () => {
            const {result} = renderUseAttributeOptionsListStateHook({
                attributeOptions: null,
            });

            let black;

            act(() => {
                black = result.current.getAttributeOption('black');
            });

            expect(black).toBeUndefined();
        });
    });

    describe('add and remove extra data', () => {
        it('it should add or remove extra data for attribute option', () => {
            const {result} = renderUseAttributeOptionsListStateHook({
                attributeOptions: givenAttributeOptions(),
            });

            expect(result.current.extraData).toEqual({});

            act(() => {
                result.current.addExtraData('black', <div>TEST BLACK</div>);
                result.current.addExtraData('blue', <div>TEST BLUE</div>);
            });

            expect(result.current.extraData).toEqual({
                black: <div>TEST BLACK</div>,
                blue: <div>TEST BLUE</div>
            });

            act(() => {
                result.current.removeExtraData('black');
            });

            expect(result.current.extraData).toEqual({
                blue: <div>TEST BLUE</div>
            });
        });

        it('it should add or remove extra data for attribute option', () => {
            const {result} = renderUseAttributeOptionsListStateHook({
                attributeOptions: null,
            });

            expect(result.current.extraData).toEqual({});

            act(() => {
                result.current.addExtraData('black', <div>TEST BLACK</div>);
                result.current.addExtraData('blue', <div>TEST BLUE</div>);
            });

            expect(result.current.extraData).toEqual({});

            act(() => {
                result.current.removeExtraData('black');
            });

            expect(result.current.extraData).toEqual({});
        });
    });
});
