import React from 'react';
import {fireEvent, render} from '@testing-library/react';

import AttributeOptionForm from 'akeneopimstructure/js/attribute-option/components/AttributeOptionForm';
import {EditingOptionContext} from 'akeneopimstructure/js/attribute-option/contexts/EditingOptionContext';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';

describe('AttributeOptionForm', () => {
  beforeEach(() => {
    jest.resetAllMocks();
  });

  const addRefMockFn = jest.fn();
  const removeRefMockFn = jest.fn();
  const onUpdateOptionLabelMockFn = jest.fn();

  const renderAttributeOptionFormWithContext = () => {
    const {option, locale, onUpdateOptionLabel} = givenProps();
    const providerState = {
      option,
      addRef: addRefMockFn,
      removeRef: removeRefMockFn,
    };

    return render(
      <ThemeProvider theme={pimTheme}>
        <EditingOptionContext.Provider value={providerState}>
          <AttributeOptionForm option={option} locale={locale} onUpdateOptionLabel={onUpdateOptionLabel} />
        </EditingOptionContext.Provider>
      </ThemeProvider>
    );
  };

  const givenProps = () => {
    return {
      option: {
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
      },
      locale: {code: 'en_US', label: 'English (United States)'},
      onUpdateOptionLabel: onUpdateOptionLabelMockFn,
    };
  };

  describe('on mount', () => {
    it('should display label and input', () => {
      const {queryByTestId, queryByText} = renderAttributeOptionFormWithContext();
      const input = queryByTestId(/attribute-option-label/i);

      expect(input).not.toBeNull();
      expect(queryByText('English (United States)')).not.toBeNull();
    });

    it('should dispatch the input was added', () => {
      const {queryByTestId} = renderAttributeOptionFormWithContext();
      const input = queryByTestId(/attribute-option-label/i);

      expect(input).not.toBeNull();
      expect(addRefMockFn).toHaveBeenCalledWith('en_US', {current: input});
    });
  });

  describe('on unmount', () => {
    it('should remove label and input', () => {
      const {unmount, queryByTestId, queryByText} = renderAttributeOptionFormWithContext();

      unmount();

      expect(queryByTestId(/attribute-option-label/i)).toBeNull();
      expect(queryByText('English (United States)')).toBeNull();
    });

    it('should dispatch the input was removed', () => {
      const {unmount} = renderAttributeOptionFormWithContext();

      unmount();
      expect(removeRefMockFn).toHaveBeenCalledWith('en_US', {current: null});
    });
  });

  it('should dispatch the option was updated when the user changes the value', () => {
    const {queryByTestId} = renderAttributeOptionFormWithContext();
    const input = queryByTestId(/attribute-option-label/i);

    // @ts-ignore
    fireEvent.change(input, {target: {value: 'Black'}});

    expect(onUpdateOptionLabelMockFn).toHaveBeenCalled();
  });
});
