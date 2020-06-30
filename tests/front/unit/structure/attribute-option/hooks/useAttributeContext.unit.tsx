import React, {PropsWithChildren} from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {useAttributeContext} from 'akeneopimstructure/js/attribute-option/hooks/useAttributeContext';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts/AttributeContext';

const renderUseAttributeContext = (useWrapper: boolean = true) => {
    const wrapper = ({children}: PropsWithChildren<any>) => (
        <AttributeContextProvider attributeId={8} autoSortOptions={false}>
            {children}
        </AttributeContextProvider>
    );

    const options = useWrapper ? {wrapper} : {};

    return renderHook(() => useAttributeContext(), options);
};

describe('useAttributeContext', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        jest.restoreAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    it('should throw an error if the attribute context is not defined', () => {
        jest.spyOn(React, 'useContext').mockImplementation(() => undefined);

        const {result} = renderUseAttributeContext(false);

        expect(result.error).not.toBeNull();
    });

    it('should return an undefined context it is not properly initialized', () => {
        const {result} = renderUseAttributeContext(false);

        expect(result.error).not.toBeNull();
    });

    it('should return the list of locales', () => {
        const {result} = renderUseAttributeContext();

        expect(result.current.attributeId).toBe(8);
        expect(result.current.autoSortOptions).toBe(false);
        expect(result.current.toggleAutoSortOptions).not.toBeUndefined();
    });
});
