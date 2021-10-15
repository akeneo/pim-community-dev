import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {TEXT_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {view as TextView} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/text';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

const textAttribute = {
  code: 'description',
  identifier: 'attribute_description',
  type: TEXT_ATTRIBUTE_TYPE,
  labels: {},
};

const textValue = {
  attribute: textAttribute,
  channel: null,
  locale: null,
  data: 'pim',
};

const textAreaAttribute = {
  code: 'description',
  identifier: 'attribute_description',
  type: TEXT_ATTRIBUTE_TYPE,
  is_textarea: true,
  labels: {},
};

const textAreaValue = {
  attribute: textAreaAttribute,
  channel: null,
  locale: null,
  data: 'My wysiwyg data',
};

test('It renders the text attribute', () => {
  renderWithProviders(
    <TextView channel={null} value={textValue} locale={null} onChange={jest.fn()} canEditData={true} />
  );

  const inputElement = screen.getByRole('textbox') as HTMLInputElement;
  expect(inputElement).toBeInTheDocument();
  expect(inputElement.value).toEqual('pim');
});

test('It renders the textarea attribute', () => {
  renderWithProviders(
    <TextView channel={null} value={textAreaValue} locale={null} onChange={jest.fn()} canEditData={true} />
  );

  const inputElement = screen.getByRole('textbox') as HTMLInputElement;
  expect(inputElement).toBeInTheDocument();
  expect(inputElement.value).toEqual('My wysiwyg data');
});

test('It renders the placeholder when the value is empty', () => {
  const emptyValue = {...textValue, data: null};

  renderWithProviders(<TextView value={emptyValue} locale={null} onChange={jest.fn()} canEditData={true} />);

  expect(screen.getByRole('textbox')).toBeInTheDocument();
});

test('It does not render if the data is not a text data', () => {
  const otherValue = {...textValue, data: {some: 'thing'}};

  renderWithProviders(<TextView value={otherValue} locale={null} onChange={jest.fn()} canEditData={true} />);

  expect(screen.queryByRole('textbox')).not.toBeInTheDocument();
});

test('It can change the text value', () => {
  const onChange = jest.fn();

  renderWithProviders(<TextView value={textValue} locale={null} onChange={onChange} canEditData={true} />);

  fireEvent.change(screen.getByRole('textbox'), {target: {value: 'pam'}});
  expect(onChange).toHaveBeenCalledWith({...textValue, data: 'pam'});
  expect(onChange).toHaveBeenCalledTimes(1);
});

test('It does not call onChange when the text value is the same', () => {
  const onChange = jest.fn();

  renderWithProviders(<TextView value={textValue} locale={null} onChange={onChange} canEditData={true} />);

  fireEvent.change(screen.getByRole('textbox'), {target: {value: 'pim'}});
  expect(onChange).not.toHaveBeenCalled();
});

test('It can change the text value on a text area attribute', () => {
  const onChange = jest.fn();
  const textAreaValue = {...textValue, attribute: {...textValue.attribute, is_textarea: true}};

  renderWithProviders(<TextView value={textAreaValue} locale={null} onChange={onChange} canEditData={true} />);

  fireEvent.change(screen.getByRole('textbox'), {target: {value: 'pam area'}});
  expect(onChange).toHaveBeenCalledWith({...textAreaValue, data: 'pam area'});
  expect(onChange).toHaveBeenCalledTimes(1);
});

test('It can submit the text value by hitting the Enter key', () => {
  const submit = jest.fn();

  renderWithProviders(
    <TextView value={textValue} locale={null} onChange={jest.fn()} onSubmit={submit} canEditData={true} />
  );

  fireEvent.keyDown(screen.getByRole('textbox'), {key: 'Enter', code: 13});
  expect(submit).toHaveBeenCalledTimes(1);
});
