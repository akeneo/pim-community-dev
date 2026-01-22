import React from 'react';
import {act, renderHook} from '@testing-library/react-hooks';
import {
  AttributeOptionFormEvent,
  EditAttributeOptionEvent,
  PIM_ATTRIBUTE_OPTION_EDITING,
  PIM_ATTRIBUTE_OPTION_LABEL_FORM_ADDED,
  PIM_ATTRIBUTE_OPTION_LABEL_FORM_REMOVED,
  useEditingOptionContextState,
} from 'akeneopimstructure/js/attribute-option/hooks/useEditingOptionContextState';

const givenOption = () => {
  return {
    id: 80,
    code: 'black',
    optionValues: {
      en_US: {
        id: 1,
        value: 'Black 2',
        locale: 'en_US',
      },
      fr_FR: {
        id: 2,
        value: 'Noir',
        locale: 'fr_FR',
      },
    },
  };
};

describe('useEditingOptionContextState', () => {
  beforeAll(() => {
    jest.spyOn(window, 'dispatchEvent').mockImplementation(() => true);
  });

  afterAll(() => {
    window.dispatchEvent.mockRestore();
  });

  it('should dispatch the option is editing', () => {
    const option = givenOption();
    const expectedEvent = new CustomEvent<EditAttributeOptionEvent>(PIM_ATTRIBUTE_OPTION_EDITING, {
      detail: {
        option,
      },
    });

    renderHook(() => useEditingOptionContextState(option));

    expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
  });

  it('should dispatch the form reference has been added', () => {
    const option = givenOption();
    const locale = 'en_US';
    const ref = React.createRef<HTMLInputElement>();

    const expectedEvent = new CustomEvent<AttributeOptionFormEvent>(PIM_ATTRIBUTE_OPTION_LABEL_FORM_ADDED, {
      detail: {
        locale,
        ref,
        code: option.code,
      },
    });

    const {result} = renderHook(() => useEditingOptionContextState(option));

    act(() => {
      result.current.addRef(locale, ref);
    });

    expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
  });

  it('should dispatch the form reference has been removed', () => {
    const option = givenOption();
    const locale = 'en_US';
    const ref = React.createRef<HTMLInputElement>();

    const expectedEvent = new CustomEvent<AttributeOptionFormEvent>(PIM_ATTRIBUTE_OPTION_LABEL_FORM_REMOVED, {
      detail: {
        locale,
        ref,
        code: option.code,
      },
    });

    const {result} = renderHook(() => useEditingOptionContextState(option));

    act(() => {
      result.current.removeRef(locale, ref);
    });

    expect(window.dispatchEvent).toHaveBeenCalledWith(expectedEvent);
  });
});
