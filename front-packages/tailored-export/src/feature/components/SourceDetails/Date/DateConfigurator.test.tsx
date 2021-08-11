import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DateConfigurator} from './DateConfigurator';
import {DateSelection, getDefaultDateSource} from './model';
import {getDefaultTextSource} from '../Text/model';

const attribute = {
  code: 'date',
  type: 'pim_catalog_date',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('./DateSelector', () => ({
  DateSelector: ({onSelectionChange}: {onSelectionChange: (updatedSelection: DateSelection) => void}) => (
    <button
      onClick={() =>
        onSelectionChange({
          format: 'dd/mm/yy',
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays a date configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <DateConfigurator
      source={{
        ...getDefaultDateSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultDateSource(attribute, null, null),
    selection: {
      format: 'dd/mm/yy',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const textAttribute = {...attribute, type: 'pim_catalog_text', code: 'text_attribute'};

  expect(() => {
    renderWithProviders(
      <DateConfigurator
        source={getDefaultTextSource(textAttribute, null, null)}
        attribute={textAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "text_attribute" for date configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
