import React, {PropsWithChildren} from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {useEditingOptionContext} from 'akeneopimstructure/js/attribute-option/hooks/useEditingOptionContext';
import {EditingOptionContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';

const renderUseEditingOptionContext = (option: AttributeOption, useWrapper: boolean = true) => {
    const wrapper = ({children}: PropsWithChildren<any>) => (
        <EditingOptionContextProvider option={option}>
            {children}
        </EditingOptionContextProvider>
    );

    const options = useWrapper ? {wrapper} : {};

    return renderHook(() => useEditingOptionContext(), options);
};

const givenAttributeOption = (): AttributeOption => {
    return {
        id: 85,
        code: 'black',
        optionValues: {
            'en_US': {id:252, locale:'en_US', value:'Black'},
            'fr_FR': {id:253, locale:'fr_FR', value:'Noir'}
        }
    };
};

describe('useEditingOptionContext', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        jest.restoreAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    it('should throw an error if the context is not defined', () => {
        jest.spyOn(React, 'useContext').mockImplementation(() => undefined);

        const option = givenAttributeOption();
        const {result} = renderUseEditingOptionContext(option, false);

        expect(result.error).not.toBeNull();
    });

    it('should return an undefined context it is not properly initialized', () => {
        const option = givenAttributeOption();
        const {result} = renderUseEditingOptionContext(option, false);

        expect(result.current.option).toBeNull();
        expect(result.current.addRef).toBeDefined();
        expect(result.current.removeRef).toBeDefined();
    });

    it('should return the editing option context', () => {
        const option = givenAttributeOption();
        const {result} = renderUseEditingOptionContext(option);

        expect(result.current.option).toBe(option);
        expect(result.current.addRef).toBeDefined();
        expect(result.current.removeRef).toBeDefined();
    });
});
