import React, {createContext, FC, RefObject, useCallback, useContext} from 'react';
import {AttributeOption} from "../model/AttributeOption.interface";

export const PIM_ATTRIBUTE_OPTION_LABEL_FORM_ADDED = 'option-form-added';
export const PIM_ATTRIBUTE_OPTION_LABEL_FORM_REMOVED = 'option-form-removed';

export type AttributeOptionFormEvent = {
    locale: string;
    code: string;
    ref: RefObject<HTMLInputElement>;
};

export type OptionFormContextState = {
    option: AttributeOption|null;
    addRef(locale: string, ref: RefObject<HTMLInputElement>): void;
    removeRef(locale: string, ref: RefObject<HTMLInputElement>): void;
};

export const OptionFormContext = createContext<OptionFormContextState>({
    option: null,
    addRef: () => {},
    removeRef: () => {},
});
OptionFormContext.displayName = 'OptionFormContext';

export const useOptionFormContext = (): OptionFormContextState => {
    return useContext(OptionFormContext);
};

type OptionFormContextProviderProps = {
    option: AttributeOption;
};

export const OptionFormContextProvider: FC<OptionFormContextProviderProps> = ({children, option}) => {
    const initialState = useOptionFormContextState(option);

    return (
        <OptionFormContext.Provider value={initialState}>{children}</OptionFormContext.Provider>
    );
};

const useOptionFormContextState = (option: AttributeOption): OptionFormContextState => {
    const handleAddRef = useCallback((locale: string, ref: React.RefObject<HTMLInputElement>) => {
        window.dispatchEvent(new CustomEvent<AttributeOptionFormEvent>(PIM_ATTRIBUTE_OPTION_LABEL_FORM_ADDED, {
            detail: {
                locale,
                code: option.code,
                ref
            }
        }));
    }, [option]);

    const handleRemoveRef = useCallback((locale: string, ref: React.RefObject<HTMLInputElement>) => {
        window.dispatchEvent(new CustomEvent<AttributeOptionFormEvent>(PIM_ATTRIBUTE_OPTION_LABEL_FORM_REMOVED, {
            detail: {
                locale,
                code: option.code,
                ref
            }
        }));
    }, [option]);

    return {
        option,
        addRef: handleAddRef,
        removeRef: handleRemoveRef
    };
};
