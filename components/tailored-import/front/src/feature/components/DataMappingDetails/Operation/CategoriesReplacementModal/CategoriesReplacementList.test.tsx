import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {CategoriesReplacementList} from './CategoriesReplacementList';
import {ValidationError} from '@akeneo-pim-community/shared';
import {Table} from 'akeneo-design-system';

test('it can update a replacement mapping', async () => {
  const handleMappingChange = jest.fn();

  const categoryTree = {
    id: 1,
    code: 'shoes',
    labels: {
      en_US: 'Shoes',
    },
  };

  await renderWithProviders(
    <Table>
      <Table.Body>
        <CategoriesReplacementList
          categoryTree={categoryTree}
          onMappingChange={handleMappingChange}
          mapping={{}}
          validationErrors={[]}
        />
      </Table.Body>
    </Table>
  );

  const [shoesInput] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.modal.table.field.to_placeholder'
  );

  userEvent.type(shoesInput, 'Chaussure;');

  expect(handleMappingChange).toHaveBeenCalledWith({
    shoes: ['Chaussure'],
  });
});

test('it displays error on validation', async () => {
  const categoryTree = {
    id: 1,
    code: 'shoes',
    labels: {
      en_US: 'Shoes',
    },
  };

  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.global',
      invalidValue: '',
      message: 'this is a global error',
      parameters: {},
      propertyPath: '',
    },
    {
      messageTemplate: 'error.key.shoes_error',
      invalidValue: 'chauss_ure',
      message: 'this is a shoes format error',
      parameters: {},
      propertyPath: '[shoes]',
    },
  ];

  await renderWithProviders(
    <Table>
      <Table.Body>
        <CategoriesReplacementList
          categoryTree={categoryTree}
          onMappingChange={jest.fn()}
          mapping={{}}
          validationErrors={validationErrors}
        />
      </Table.Body>
    </Table>
  );

  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
  expect(screen.getByText('error.key.shoes_error')).toBeInTheDocument();
});
