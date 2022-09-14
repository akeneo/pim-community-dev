import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {CategoriesReplacementModal} from './CategoriesReplacementModal';
import {Category, CategoryTree} from '../../../../models';

const operationUuid = 'b26bcde7-1231-47cc-84ba-e014bb08fbd5';
const categoryTrees: CategoryTree[] = [
  {
    id: 1,
    code: 'shoes',
    labels: {
      en_US: 'Shoes',
    },
    has_error: true,
  },
  {
    id: 2,
    code: 'tshirt',
    labels: {
      en_US: 'T-Shirt',
    },
    has_error: false,
  },
  {
    id: 3,
    code: 'ceinturon',
    labels: {
      en_US: 'Ceinturone',
    },
    has_error: false,
  },
];

const categoryChildrenFetcher = (): Category[] => {
  return [];
};

jest.mock('../../../../hooks/useCategoryChildrenFetcher', () => ({
  useCategoryChildrenFetcher: () => categoryChildrenFetcher,
}));

jest.mock('../../../../hooks/useCategoryTrees', () => {
  return {
    useCategoryTrees: () => categoryTrees,
  };
});

const validResponse = {
  ok: true,
  json: async () => {},
};

test('it can update a replacement mapping', async () => {
  const handleConfirm = jest.fn();
  global.fetch = jest.fn().mockImplementation(async () => validResponse);

  await renderWithProviders(
    <CategoriesReplacementModal
      operationUuid={operationUuid}
      initialMapping={{}}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  const [shoesInput] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder'
  );

  userEvent.type(shoesInput, 'CHAUSSURE{enter}');
  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).toHaveBeenCalledWith({
    shoes: ['CHAUSSURE'],
  });
});

test('it validates replacement mapping before confirming', async () => {
  const handleConfirm = jest.fn();
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
    json: async () => [
      {propertyPath: '[mapping][shoes]', messageTemplate: 'error.invalid_value.message', parameters: {}},
    ],
  }));

  await renderWithProviders(
    <CategoriesReplacementModal
      operationUuid={operationUuid}
      initialMapping={{}}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  const [shoesInput] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder'
  );

  userEvent.type(shoesInput, 'invalid_mapping');
  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).not.toHaveBeenCalled();
  expect(screen.getByText('error.invalid_value.message')).toBeInTheDocument();
});

test('it can change mapping on another category tree', async () => {
  const handleConfirm = jest.fn();
  global.fetch = jest.fn().mockImplementation(async () => validResponse);

  await renderWithProviders(
    <CategoriesReplacementModal
      operationUuid={operationUuid}
      initialMapping={{
        shoes: ['CHAUSSURE'],
      }}
      onConfirm={handleConfirm}
      onCancel={jest.fn()}
    />
  );

  userEvent.click(screen.getByText('T-Shirt'));

  const [tshirtInput] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder'
  );

  userEvent.type(tshirtInput, 'MAILLOT DE CORPS{enter}');
  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleConfirm).toHaveBeenCalledWith({
    shoes: ['CHAUSSURE'],
    tshirt: ['MAILLOT DE CORPS'],
  });
});
