import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {FamilyReplacementOperationBlock, getDefaultFamilyReplacementOperation} from './FamilyReplacementOperationBlock';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {OperationPreviewData} from '../../../../models';

const operationPreviewData: OperationPreviewData = {
  [expect.any(String)]: [
    {type: 'string', value: 'foo'},
    {type: 'string', value: 'bar'},
  ],
};

jest.mock('../../../../hooks/useFamilies', () => ({
  useFamilies: (
    search: string,
    page: number,
    familyCodesToInclude: string[] | null,
    familyCodesToExclude: string[] | null,
    shouldFetch: boolean
  ) => [
    [
      {
        code: 'clothing',
        labels: {
          en_US: 'clothing',
        },
      },
      {
        code: 'Camcorders',
        labels: {
          en_US: 'Camcorders',
        },
      },
      {
        code: 'Headphones',
        labels: {
          en_US: 'Headphones',
        },
      },
    ].filter(
      ({code}) =>
        code.includes(search) &&
        (null === familyCodesToInclude || familyCodesToInclude.includes(code)) &&
        (null === familyCodesToExclude || !familyCodesToExclude.includes(code))
    ),
    3,
  ],
}));

test('it can get the default family replacement operation', () => {
  expect(getDefaultFamilyReplacementOperation()).toEqual({
    uuid: expect.any(String),
    type: 'family_replacement',
    mapping: {},
  });
});

test('it displays a family_replacement operation block', () => {
  renderWithProviders(
    <FamilyReplacementOperationBlock
      targetCode="family"
      operation={{uuid: expect.any(String), type: 'family_replacement', mapping: {}}}
      onChange={jest.fn()}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: operationPreviewData,
      }}
      validationErrors={[]}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.family_replacement.title')
  ).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <FamilyReplacementOperationBlock
      targetCode="family"
      operation={{uuid: expect.any(String), type: 'family_replacement', mapping: {}}}
      onChange={jest.fn()}
      onRemove={handleRemove}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: operationPreviewData,
      }}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.delete'));

  expect(handleRemove).toHaveBeenCalledWith('family_replacement');
});

test('it opens a replacement modal and handles change', async () => {
  const handleChange = jest.fn();

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => {},
  }));

  renderWithProviders(
    <FamilyReplacementOperationBlock
      targetCode="family"
      operation={{uuid: expect.any(String), type: 'family_replacement', mapping: {}}}
      onChange={handleChange}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: operationPreviewData,
      }}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));

  expect(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.family_replacement.title')
  ).toBeInTheDocument();

  const [fooBarMapping] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder'
  );

  userEvent.type(fooBarMapping, 'foo{enter}bar{enter}');

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleChange).toHaveBeenCalledWith({
    uuid: expect.any(String),
    type: 'family_replacement',
    mapping: {
      clothing: ['foo', 'bar'],
    },
  });
});

test('it does not call handler when cancelling', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <FamilyReplacementOperationBlock
      targetCode="family"
      operation={{uuid: expect.any(String), type: 'family_replacement', mapping: {}}}
      onChange={handleChange}
      onRemove={jest.fn()}
      isLastOperation={false}
      previewData={{
        isLoading: false,
        hasError: false,
        data: operationPreviewData,
      }}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByText('pim_common.edit'));
  userEvent.click(screen.getByTitle('pim_common.close'));

  expect(handleChange).not.toHaveBeenCalled();
});

test('it throws an error if the operation is not a family replacement operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <FamilyReplacementOperationBlock
        targetCode="family"
        operation={{uuid: expect.any(String), modes: ['remove'], type: 'clean_html'}}
        onChange={jest.fn()}
        onRemove={jest.fn()}
        isLastOperation={false}
        previewData={{
          isLoading: false,
          hasError: false,
          data: operationPreviewData,
        }}
        validationErrors={[]}
      />
    );
  }).toThrowError('FamilyReplacementOperationBlock can only be used with FamilyReplacementOperation');

  mockedConsole.mockRestore();
});
