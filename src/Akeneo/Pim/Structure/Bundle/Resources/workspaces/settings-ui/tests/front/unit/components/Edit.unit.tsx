import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByRole, queryAllByTestId} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import Edit from 'akeneopimstructure/js/attribute-option/components/Edit';
import {AttributeContextProvider, LocalesContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

let container: HTMLElement;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

const option: AttributeOption = {
  id: 80,
  code: 'black',
  optionValues: {
    en_US: {id: 3, value: 'Black', locale: 'en_US'},
    fr_FR: {id: 4, value: 'Noir', locale: 'fr_FR'},
  },
  toImprove: undefined,
};

describe('Edit an attribute option', () => {
  test('it renders an option form', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() => {
      return {
        json: () => [
          {code: 'en_US', label: 'English (United States)'},
          {code: 'fr_FR', label: 'French (France)'},
        ],
      };
    });

    const saveCallback = jest.fn();

    await act(async () => {
      ReactDOM.render(
        <DependenciesProvider>
          <ThemeProvider theme={pimTheme}>
            <AttributeContextProvider attributeId={8} autoSortOptions={true}>
              <LocalesContextProvider>
                <Edit option={option} saveAttributeOption={saveCallback} />
              </LocalesContextProvider>
            </AttributeContextProvider>
          </ThemeProvider>
        </DependenciesProvider>,
        container
      );
    });

    const translations = queryAllByTestId(container, 'attribute-option-label');
    expect(translations.length).toBe(2);

    const saveButton = getByRole(container, 'save-options-translations');
    expect(saveButton).toBeInTheDocument();

    await fireEvent.change(translations[0], {target: {value: 'Black 2'}});
    await fireEvent.click(saveButton);
    let expectedOption: AttributeOption = expect.objectContaining({
      id: 80,
      code: 'black',
      optionValues: {
        en_US: {
          id: 3,
          value: 'Black 2',
          locale: 'en_US',
        },
        fr_FR: {
          id: 4,
          value: 'Noir',
          locale: 'fr_FR',
        },
      },
      toImprove: undefined,
    });
    expect(saveCallback).toHaveBeenNthCalledWith(1, expectedOption);
  });
});
