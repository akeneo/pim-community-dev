import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {AddAttributeDropdown} from './AddAttributeDropdown';

const attributes = [
  {
    identifier: 'description_uuid',
    code: 'description',
    labels: {'en_US': 'Description'},
    is_read_only: false,
    type: "text",
  },
  {
    identifier: 'name_uuid',
    code: 'name',
    labels: {'en_US': 'Name'},
    is_read_only: false,
    type: "text",
  },
  {
    identifier: 'identifier_uuid',
    code: 'identifier',
    labels: {en_US: 'Identifier'},
    is_read_only: true,
    type: "text",
  },
  {
    identifier: 'color_uuid',
    code: 'color',
    labels: {},
    is_read_only: false,
    type: "text",
  },
];

test('it renders all not read only attribute properly', () => {
  renderWithProviders(
    <AddAttributeDropdown
      attributes={attributes}
      uiLocale='en_US'
      alreadyUsed={[]}
      onAdd={() => {}}
    />
  );

  const dropdownButton = screen.getByText('Add attributes');
  expect(dropdownButton).toBeInTheDocument();
  fireEvent.click(dropdownButton);

  expect(screen.getByText('Description')).toBeInTheDocument();
  expect(screen.getByText('Name')).toBeInTheDocument();
  expect(screen.getByText('[color]')).toBeInTheDocument();
  expect(screen.queryByText('Identifier')).not.toBeInTheDocument();
});

test('it call onAdd handler when user select an attribute', () => {
  const handleAddAttribute = jest.fn();
  renderWithProviders(
    <AddAttributeDropdown
      attributes={attributes}
      uiLocale='en_US'
      alreadyUsed={[]}
      onAdd={handleAddAttribute}
    />
  );

  const dropdownButton = screen.getByText('Add attributes');
  expect(dropdownButton).toBeInTheDocument();
  fireEvent.click(dropdownButton);
  const attributeToAdd = screen.getByText('Name');
  fireEvent.click(attributeToAdd);

  expect(handleAddAttribute).toHaveBeenCalledWith(attributes[1])
});
