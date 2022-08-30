import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {
  getDefaultChangeCaseOperation,
  ChangeCaseOperationBlock,
  isChangeCaseOperation,
} from './ChangeCaseOperationBlock';
import {OperationPreviewData} from 'feature/models';

const operationPreviewData: OperationPreviewData = {
  [expect.any(String)]: [
    {type: 'string', value: 'I am a test'},
    {type: 'string', value: 'I also am a test'},
  ],
};

test('it says if if is a change case operation', () => {
  expect(
    isChangeCaseOperation({
      uuid: expect.any(String),
      type: 'split',
      separator: ',',
    })
  ).toBe(false);

  expect(
    isChangeCaseOperation({
      uuid: expect.any(String),
      type: 'change_case',
      mode: 'unknown',
    })
  ).toBe(false);

  expect(
    isChangeCaseOperation({
      uuid: expect.any(String),
      type: 'change_case',
      mode: 'uppercase',
    })
  ).toBe(true);
});

test('it can get the default change case operation', () => {
  expect(getDefaultChangeCaseOperation()).toEqual({
    uuid: expect.any(String),
    type: 'change_case',
    mode: 'uppercase',
  });
});

test('it displays a change case operation block', () => {
  renderWithProviders(
    <ChangeCaseOperationBlock
      targetCode="description"
      operation={{uuid: expect.any(String), type: 'change_case', mode: 'uppercase'}}
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

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.change_case.title')).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <ChangeCaseOperationBlock
      targetCode="description"
      operation={{uuid: expect.any(String), type: 'change_case', mode: 'uppercase'}}
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

  expect(handleRemove).toHaveBeenCalledWith('change_case');
});

test('it can change the mode', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <ChangeCaseOperationBlock
      targetCode="description"
      operation={{uuid: expect.any(String), type: 'change_case', mode: 'uppercase'}}
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

  userEvent.click(screen.getByTitle('akeneo.tailored_import.data_mapping.operations.common.collapse'));
  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByTitle('lowercase'));

  expect(handleChange).toHaveBeenCalledWith({uuid: expect.any(String), type: 'change_case', mode: 'lowercase'});
});

test('it throws an error if the operation is not a change case operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <ChangeCaseOperationBlock
        targetCode="description"
        operation={{uuid: expect.any(String), type: 'split', separator: ';'}}
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
  }).toThrowError('ChangeCaseOperationBlock can only be used with ChangeCaseOperation');

  mockedConsole.mockRestore();
});
