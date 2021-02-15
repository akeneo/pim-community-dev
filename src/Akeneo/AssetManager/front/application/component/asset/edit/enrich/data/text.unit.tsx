import * as React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {TEXT_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {view as TextView} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/text';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

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

test('It renders the text attribute', () => {
  renderWithProviders(
    <TextView
      value={textValue}
      locale={null}
      onChange={() => {}}
      onSubmit={() => {}}
      canEditData={true}
    />
  );

  const inputElement = screen.getByRole('textbox') as HTMLInputElement;
  expect(inputElement).toBeInTheDocument();
  expect(inputElement.value).toEqual('pim');
});

test('It renders the placeholder when the value is empty', () => {
  const emptyValue = {...textValue, data: null};
  renderWithProviders(
    <TextView
      value={emptyValue}
      locale={null}
      onChange={() => {}}
      onSubmit={() => {}}
      canEditData={true}
    />
  );

  expect(screen.getByRole('textbox')).toBeInTheDocument();
});

test('It does not render if the data is not a text data', () => {
  const otherValue = {...textValue, data: {some: 'thing'}};
  renderWithProviders(
    <TextView
      value={otherValue}
      locale={null}
      onChange={() => {}}
      onSubmit={() => {}}
      canEditData={true}
    />
  );

  expect(screen.queryByRole('textbox')).not.toBeInTheDocument();
});

test('It can change the text value', () => {
  let editionValue = textValue;
  const change = jest.fn().mockImplementationOnce(value => (editionValue = value));
  renderWithProviders(
    <TextView
      value={editionValue}
      locale={null}
      onChange={change}
      onSubmit={() => {}}
      canEditData={true}
    />
  );

  const inputElement = screen.getByRole('textbox');

  fireEvent.change(inputElement, {target: {value: 'pam'}});
  expect(editionValue.data).toEqual('pam');
  expect(change).toHaveBeenCalledTimes(1);
});

test('It can submit the text value by hitting the Enter key', () => {
  const submit = jest.fn().mockImplementationOnce(() => {});
  renderWithProviders(
    <TextView
      value={textValue}
      locale={null}
      onChange={() => {}}
      onSubmit={submit}
      canEditData={true}
    />
  );

  const inputElement = screen.getByRole('textbox');
  fireEvent.keyDown(inputElement, {key: 'Enter', code: 13});
  expect(submit).toHaveBeenCalledTimes(1);
});
