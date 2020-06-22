import React from 'react';
import {act, renderHook} from '@testing-library/react-hooks';

import useAttributeContextState from 'akeneopimstructure/js/attribute-option/hooks/useAttributeContextState';
import {ATTRIBUTE_OPTIONS_AUTO_SORT} from "../../../../../../src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/model";

const renderUseAttributeContextState = (attributeId: number, autoSort: boolean) => {
    return renderHook(() => useAttributeContextState(attributeId, autoSort));
};

describe('useAttributeContextState', () => {
    beforeAll(() => {
        jest.spyOn(window, 'dispatchEvent').mockImplementation(() => true);
    });

    afterAll(() => {
        window.dispatchEvent.mockRestore();
    });

    it('it should dispatch the autoSort value has been toggled', () => {
        const expectedEvent = new CustomEvent(ATTRIBUTE_OPTIONS_AUTO_SORT, {detail: {
            autoSortOptions: true,
        }});
        const {result} = renderUseAttributeContextState(8, false);

        expect(result.current.attributeId).toBe(8);
        expect(result.current.autoSortOptions).toBe(false);

        act(() => {
            result.current.toggleAutoSortOptions();
        });

        expect(result.current.autoSortOptions).toBe(true);
        expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
    });
});
