import React, {RefObject, useCallback, useEffect} from 'react';
import {AttributeOption} from '../model';

export const PIM_ATTRIBUTE_OPTION_LABEL_FORM_ADDED = 'option-form-added';
export const PIM_ATTRIBUTE_OPTION_LABEL_FORM_REMOVED = 'option-form-removed';
export const PIM_ATTRIBUTE_OPTION_EDITING = 'option-editing';

export type AttributeOptionFormEvent = {
  locale: string;
  code: string;
  ref: RefObject<HTMLInputElement>;
};

export type EditAttributeOptionEvent = {
  option: AttributeOption;
};

export type EditingOptionContextState = {
  option: AttributeOption | null;
  addRef(locale: string, ref: RefObject<HTMLInputElement>): void;
  removeRef(locale: string, ref: RefObject<HTMLInputElement>): void;
};

export const useEditingOptionContextState = (option: AttributeOption): EditingOptionContextState => {
  const handleAddRef = useCallback(
    (locale: string, ref: React.RefObject<HTMLInputElement>) => {
      window.dispatchEvent(
        new CustomEvent<AttributeOptionFormEvent>(PIM_ATTRIBUTE_OPTION_LABEL_FORM_ADDED, {
          detail: {
            locale,
            code: option.code,
            ref,
          },
        })
      );
    },
    [option]
  );

  const handleRemoveRef = useCallback(
    (locale: string, ref: React.RefObject<HTMLInputElement>) => {
      window.dispatchEvent(
        new CustomEvent<AttributeOptionFormEvent>(PIM_ATTRIBUTE_OPTION_LABEL_FORM_REMOVED, {
          detail: {
            locale,
            code: option.code,
            ref,
          },
        })
      );
    },
    [option]
  );

  useEffect(() => {
    window.dispatchEvent(
      new CustomEvent<EditAttributeOptionEvent>(PIM_ATTRIBUTE_OPTION_EDITING, {
        detail: {
          option,
        },
      })
    );
  }, [option]);

  return {
    option,
    addRef: handleAddRef,
    removeRef: handleRemoveRef,
  };
};
