import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {CategoryReplacementRow} from './CategoryReplacementRow';
import {ValidationError} from '@akeneo-pim-community/shared';
import {Table} from 'akeneo-design-system';
import {Category} from '../../../../models';

const category = {
  id: 1,
  code: 'shoes',
  label: 'Shoes',
  isLeaf: false,
};

const categoryChildrenFetcher = (categoryId: number): Category[] => {
  switch (categoryId) {
    case 1:
      return [
        {
          id: 2,
          label: 'Sandalette',
          code: 'sandalette',
          isLeaf: true,
        },
        {
          id: 3,
          label: 'Botte',
          code: 'botte',
          isLeaf: false,
        },
      ];
    case 3:
      return [
        {
          id: 4,
          code: 'bottine',
          label: 'Bottine',
          isLeaf: true,
        },
      ];
    default:
      throw new Error('Unexpected call');
  }
};

jest.mock('../../../../hooks/useCategoryChildrenFetcher', () => ({
  useCategoryChildrenFetcher: () => categoryChildrenFetcher,
}));

test('it display category tree and first level of category', async () => {
  const handleMappingChange = jest.fn();
  await renderWithProviders(
    <Table>
      <Table.Body>
        <CategoryReplacementRow
          tree={category}
          onMappingChange={handleMappingChange}
          mapping={{}}
          validationErrors={[]}
          level={0}
        />
      </Table.Body>
    </Table>
  );

  expect(screen.getByText('Shoes')).toBeInTheDocument();
  expect(screen.getByText('Sandalette')).toBeInTheDocument();
  expect(screen.getByText('Botte')).toBeInTheDocument();
  expect(screen.queryByText('Bottine')).not.toBeInTheDocument();
});

test('it can update a replacement mapping', async () => {
  const handleMappingChange = jest.fn();

  await renderWithProviders(
    <Table>
      <Table.Body>
        <CategoryReplacementRow
          tree={category}
          onMappingChange={handleMappingChange}
          mapping={{}}
          validationErrors={[]}
          level={0}
        />
      </Table.Body>
    </Table>
  );

  const [shoesInput] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder'
  );

  userEvent.type(shoesInput, 'Chaussure;');

  expect(handleMappingChange).toHaveBeenCalledWith({
    shoes: ['Chaussure'],
  });
});

test('it display category children when user click on a closed category', async () => {
  const handleMappingChange = jest.fn();
  await renderWithProviders(
    <Table>
      <Table.Body>
        <CategoryReplacementRow
          tree={category}
          onMappingChange={handleMappingChange}
          mapping={{}}
          validationErrors={[]}
          level={0}
        />
      </Table.Body>
    </Table>
  );

  expect(screen.queryByText('Bottine')).not.toBeInTheDocument();
  await act(async () => {
    await userEvent.click(screen.getByText('Botte'));
  });

  expect(screen.getByText('Bottine')).toBeInTheDocument();
});

test('it close category children when user click on a opened category', async () => {
  const handleMappingChange = jest.fn();
  await renderWithProviders(
    <Table>
      <Table.Body>
        <CategoryReplacementRow
          tree={category}
          onMappingChange={handleMappingChange}
          mapping={{}}
          validationErrors={[]}
          level={0}
        />
      </Table.Body>
    </Table>
  );

  expect(screen.getByText('Sandalette')).toBeInTheDocument();
  expect(screen.getByText('Botte')).toBeInTheDocument();
  userEvent.click(screen.getByText('Shoes'));
  expect(screen.queryByText('Sandalette')).not.toBeInTheDocument();
  expect(screen.queryByText('Botte')).not.toBeInTheDocument();
});

test('it displays error on validation', async () => {
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
        <CategoryReplacementRow
          tree={category}
          onMappingChange={jest.fn()}
          mapping={{}}
          validationErrors={validationErrors}
          level={0}
        />
      </Table.Body>
    </Table>
  );

  expect(screen.queryByText('error.key.global')).not.toBeInTheDocument();
  expect(screen.getByText('error.key.shoes_error')).toBeInTheDocument();
});
