import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {
  getDefaultCategoryReplacementOperation,
  CategoryReplacementOperationBlock,
} from './CategoryReplacementOperationBlock';

jest.mock('../../../../hooks/useCategoryTrees', () => ({
  useCategoryTrees: () => [
    {
      id: 1,
      code: 'shoes',
      labels: {
        en_US: 'Shoes',
      },
    },
    {
      id: 2,
      code: 'tshirt',
      labels: {
        en_US: 'T-Shirt',
      },
    },
    {
      id: 3,
      code: 'ceinturon',
      labels: {
        en_US: 'Ceinturone',
      },
    },
  ],
}));

test('it can get the default category replacement operation', () => {
  expect(getDefaultCategoryReplacementOperation()).toEqual({
    uuid: expect.any(String),
    type: 'category_replacement',
    mapping: {},
  });
});

test('it displays a category_replacement operation block', () => {
  const previewData = ['<p>TSHIRT</p>', '<p>TeeShirt</p>'];

  renderWithProviders(
    <CategoryReplacementOperationBlock
      targetCode="category"
      operation={{uuid: expect.any(String), type: 'category_replacement', mapping: {}}}
      onChange={jest.fn()}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: previewData,
      }}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.category_replacement.title')
  ).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();
  const previewData = ['<p>Hello</p>', '<p>World</p>'];

  renderWithProviders(
    <CategoryReplacementOperationBlock
      targetCode="category"
      operation={{uuid: expect.any(String), type: 'category_replacement', mapping: {}}}
      onChange={jest.fn()}
      onRemove={handleRemove}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: previewData,
      }}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.delete'));

  expect(handleRemove).toHaveBeenCalledWith('category_replacement');
});

test('it opens a replacement modal and handles change', async () => {
  const handleChange = jest.fn();
  const previewData = ['<p>Pantalon</p>', '<p>Pantacourt</p>'];

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => {},
  }));

  renderWithProviders(
    <CategoryReplacementOperationBlock
      targetCode="category"
      operation={{uuid: expect.any(String), type: 'category_replacement', mapping: {}}}
      onChange={handleChange}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: previewData,
      }}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.category_replacement.modal.title')
  ).toBeInTheDocument();

  const [shoesMapping] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.modal.table.field.to_placeholder'
  );

  userEvent.type(shoesMapping, 'chaussure;chaussures en daim;');

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleChange).toHaveBeenCalledWith({
    uuid: expect.any(String),
    type: 'category_replacement',
    mapping: {
      shoes: ['chaussure', 'chaussures en daim'],
    },
  });
});

test('it does not call handler when cancelling', () => {
  const handleChange = jest.fn();
  const previewData = ['<p>Cape</p>', '<p>Cape en velour</p>'];

  renderWithProviders(
    <CategoryReplacementOperationBlock
      targetCode="category"
      operation={{uuid: expect.any(String), type: 'category_replacement', mapping: {}}}
      onChange={handleChange}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: previewData,
      }}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));
  userEvent.click(screen.getByTitle('pim_common.close'));

  expect(handleChange).not.toHaveBeenCalled();
});

test('it throws an error if the operation is not a category replacement operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const previewData = ['<p>Chaussettes</p>', '<p>Sandalettes</p>'];

  expect(() => {
    renderWithProviders(
      <CategoryReplacementOperationBlock
        targetCode="category"
        operation={{uuid: expect.any(String), type: 'clean_html_tags'}}
        onChange={jest.fn()}
        onRemove={jest.fn()}
        isLastOperation={false}
        previewData={{
          isLoading: false,
          hasError: false,
          data: previewData,
        }}
      />
    );
  }).toThrowError('CategoryReplacementOperationBlock can only be used with CategoryReplacementOperation');

  mockedConsole.mockRestore();
});
