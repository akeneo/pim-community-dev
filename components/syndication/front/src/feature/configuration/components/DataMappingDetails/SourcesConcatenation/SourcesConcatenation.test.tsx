import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '../../../tests';
import {Format, Source} from '../../../models';
import {SourcesConcatenation} from './SourcesConcatenation';

const sources: Source[] = [
  {
    uuid: 'description-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: null,
    channel: null,
    operations: {},
    selection: {
      type: 'code',
    },
  },
  {
    uuid: 'parent-1e40-4c55-a415-89c7958b270d',
    code: 'parent',
    type: 'property',
    locale: null,
    channel: null,
    operations: {},
    selection: {
      type: 'code',
    },
  },
];

const format: Format = {
  type: 'concat',
  space_between: true,
  elements: [
    {
      type: 'source',
      uuid: 'description-1e40-4c55-a415-89c7958b270d',
      value: 'description-1e40-4c55-a415-89c7958b270d',
    },
    {
      type: 'text',
      uuid: 'text-1e40-4c55-a415-89c7958b270d',
      value: 'some text',
    },
    {
      type: 'source',
      uuid: 'parent-1e40-4c55-a415-89c7958b270d',
      value: 'parent-1e40-4c55-a415-89c7958b270d',
    },
  ],
};

test('it can add a text element when clicking the add text button', async () => {
  const onFormatChange = jest.fn();

  await renderWithProviders(
    <SourcesConcatenation validationErrors={[]} sources={sources} format={format} onFormatChange={onFormatChange} />
  );

  userEvent.click(screen.getByText('akeneo.syndication.data_mapping_details.concatenation.add_text'));

  expect(onFormatChange).toHaveBeenCalledWith({
    ...format,
    elements: [
      ...format.elements,
      {
        type: 'text',
        uuid: expect.any(String),
        value: '',
      },
    ],
  });
});

test('it disables add text button when max text limit is reached', async () => {
  const onFormatChange = jest.fn();

  await renderWithProviders(
    <SourcesConcatenation
      validationErrors={[]}
      sources={sources}
      format={{
        ...format,
        elements: [...Array(10)].map((_e, index) => ({
          type: 'text',
          uuid: index.toString(),
          value: 'x',
        })),
      }}
      onFormatChange={onFormatChange}
    />
  );

  const addTextButton = screen.getByText('akeneo.syndication.data_mapping_details.concatenation.add_text');

  expect(addTextButton).toBeDisabled();

  userEvent.click(addTextButton);

  expect(onFormatChange).not.toHaveBeenCalled();
});

test('it can remove a text element when clicking the corresponding remove button', async () => {
  const onFormatChange = jest.fn();

  await renderWithProviders(
    <SourcesConcatenation validationErrors={[]} sources={sources} format={format} onFormatChange={onFormatChange} />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(onFormatChange).toHaveBeenCalledWith({
    ...format,
    elements: [
      {
        type: 'source',
        uuid: 'description-1e40-4c55-a415-89c7958b270d',
        value: 'description-1e40-4c55-a415-89c7958b270d',
      },
      {
        type: 'source',
        uuid: 'parent-1e40-4c55-a415-89c7958b270d',
        value: 'parent-1e40-4c55-a415-89c7958b270d',
      },
    ],
  });
});

test('it can reorder concat elements with drag and drop', async () => {
  const onFormatChange = jest.fn();

  await renderWithProviders(
    <SourcesConcatenation validationErrors={[]} sources={sources} format={format} onFormatChange={onFormatChange} />
  );

  let dataTransferred = '';
  const dataTransfer = {
    getData: (_format: string) => {
      return dataTransferred;
    },
    setData: (_format: string, data: string) => {
      dataTransferred = data;
    },
  };

  fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[1]);
  fireEvent.dragStart(screen.getAllByRole('row')[1], {dataTransfer});
  fireEvent.dragEnter(screen.getAllByRole('row')[2], {dataTransfer});
  fireEvent.dragLeave(screen.getAllByRole('row')[2], {dataTransfer});
  fireEvent.drop(screen.getAllByRole('row')[2], {dataTransfer});
  fireEvent.dragEnd(screen.getAllByRole('row')[1], {dataTransfer});

  expect(onFormatChange).toHaveBeenCalledWith({
    ...format,
    elements: [
      {
        type: 'source',
        uuid: 'description-1e40-4c55-a415-89c7958b270d',
        value: 'description-1e40-4c55-a415-89c7958b270d',
      },
      {
        type: 'source',
        uuid: 'parent-1e40-4c55-a415-89c7958b270d',
        value: 'parent-1e40-4c55-a415-89c7958b270d',
      },
      {
        type: 'text',
        uuid: 'text-1e40-4c55-a415-89c7958b270d',
        value: 'some text',
      },
    ],
  });
});

test('it can update the value of a text element', async () => {
  const onFormatChange = jest.fn();

  await renderWithProviders(
    <SourcesConcatenation validationErrors={[]} sources={sources} format={format} onFormatChange={onFormatChange} />
  );

  userEvent.type(
    screen.getByPlaceholderText('akeneo.syndication.data_mapping_details.concatenation.text_placeholder'),
    'e'
  );

  expect(onFormatChange).toHaveBeenCalledWith({
    ...format,
    elements: [
      {
        type: 'source',
        uuid: 'description-1e40-4c55-a415-89c7958b270d',
        value: 'description-1e40-4c55-a415-89c7958b270d',
      },
      {
        type: 'text',
        uuid: 'text-1e40-4c55-a415-89c7958b270d',
        value: 'some texte',
      },
      {
        type: 'source',
        uuid: 'parent-1e40-4c55-a415-89c7958b270d',
        value: 'parent-1e40-4c55-a415-89c7958b270d',
      },
    ],
  });
});

test('it can change the space between property when clicking on the space between checkbox', async () => {
  const onFormatChange = jest.fn();

  await renderWithProviders(
    <SourcesConcatenation validationErrors={[]} sources={sources} format={format} onFormatChange={onFormatChange} />
  );

  userEvent.click(screen.getByLabelText('akeneo.syndication.data_mapping_details.concatenation.space_between'));

  expect(onFormatChange).toHaveBeenCalledWith({
    ...format,
    space_between: false,
  });
});

test('it displays concat elements validation errors', async () => {
  await renderWithProviders(
    <SourcesConcatenation
      validationErrors={[
        {
          messageTemplate: 'error.key.elements',
          invalidValue: '',
          message: 'this is an elements error',
          parameters: {},
          propertyPath: '[elements]',
        },
      ]}
      sources={sources}
      format={format}
      onFormatChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.elements')).toBeInTheDocument();
});
