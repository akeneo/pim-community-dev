import React from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {
  getDefaultSimpleReferenceEntityReplacementOperation,
  SimpleReferenceEntityReplacementOperationBlock,
} from './SimpleReferenceEntityReplacementOperationBlock';
import {OperationPreviewData} from 'feature/models';

const operationPreviewData: OperationPreviewData = {
  [expect.any(String)]: [
    {type: 'string', value: 'alessis'},
    {type: 'string', value: 'adidass'},
  ],
};

jest.mock('../../../../hooks/useRecords', () => ({
  useRecords: () => [
    [
      {
        code: 'alessis',
        labels: {
          en_US: 'Alessis',
        },
      },
      {
        code: 'adiddas',
        labels: {
          en_US: 'Adidass',
        },
      },
    ],
    2,
  ],
}));

test('it can get the simple reference entity replacement operation', () => {
  expect(getDefaultSimpleReferenceEntityReplacementOperation()).toEqual({
    uuid: expect.any(String),
    type: 'simple_reference_entity_replacement',
    mapping: {},
  });
});

test('it displays a simple reference entity replacement operation block', () => {
  renderWithProviders(
    <SimpleReferenceEntityReplacementOperationBlock
      targetCode="brand"
      targetReferenceDataName="simple_link_reference"
      operation={{uuid: expect.any(String), type: 'simple_reference_entity_replacement', mapping: {}}}
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
    screen.getByText('akeneo.tailored_import.data_mapping.operations.simple_reference_entity_replacement.title')
  ).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <SimpleReferenceEntityReplacementOperationBlock
      targetCode="brand"
      targetReferenceDataName="simple_link_reference"
      operation={{uuid: expect.any(String), type: 'simple_reference_entity_replacement', mapping: {}}}
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

  expect(handleRemove).toHaveBeenCalledWith('simple_reference_entity_replacement');
});

test('it opens a replacement modal and handles change', async () => {
  const handleChange = jest.fn();

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => {},
  }));

  renderWithProviders(
    <SimpleReferenceEntityReplacementOperationBlock
      targetCode="brand"
      targetReferenceDataName="simple_link_reference"
      operation={{uuid: expect.any(String), type: 'simple_reference_entity_replacement', mapping: {}}}
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
    screen.getByText('akeneo.tailored_import.data_mapping.operations.replacement.modal.records')
  ).toBeInTheDocument();

  const [alessisMapping] = screen.getAllByPlaceholderText(
    'akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder'
  );

  userEvent.type(alessisMapping, 'sweet{enter}');

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.confirm'));
  });

  expect(handleChange).toHaveBeenCalledWith({
    uuid: expect.any(String),
    type: 'simple_reference_entity_replacement',
    mapping: {
      alessis: ['sweet'],
    },
  });
});

test('it does not call handler when cancelling', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <SimpleReferenceEntityReplacementOperationBlock
      targetCode="brand"
      targetReferenceDataName="simple_link_reference"
      operation={{uuid: expect.any(String), type: 'simple_reference_entity_replacement', mapping: {}}}
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

test('it throws an error if the operation is not a simple select replacement operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <SimpleReferenceEntityReplacementOperationBlock
        targetCode="brand"
        targetReferenceDataName="simple_link_reference"
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
  }).toThrowError(
    'SimpleReferenceEntityReplacementOperationBlock can only be used with SimpleReferenceEntityReplacementOperation'
  );

  mockedConsole.mockRestore();
});

test('it throws an error if reference data name is not provided', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <SimpleReferenceEntityReplacementOperationBlock
        targetCode="brand"
        operation={{uuid: expect.any(String), type: 'simple_reference_entity_replacement', mapping: {}}}
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
  }).toThrowError('Missing Reference Data name in attribute');

  mockedConsole.mockRestore();
});
