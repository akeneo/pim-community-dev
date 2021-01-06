import '@testing-library/jest-dom';
import React from 'react';
import {fireEvent, screen, act} from '@testing-library/react';
import {dependencies} from '@akeneo-pim-community/legacy-bridge/src/provider/dependencies';
import {DeleteModal} from 'pimui/js/attribute/form/delete/DeleteModal';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

beforeAll(() =>
  global.fetch.mockImplementation(async (url: string) => {
    switch (url) {
      case 'pim_enrich_count_items_with_attribute_value':
        return {ok: true, json: () => ({products: 3, product_models: 5})};
      case 'pim_enrich_attribute_rest_remove':
      default:
        return {ok: true};
    }
  })
);

afterAll(() => {
  global.fetch && global.fetch.mockClear();
});

test('it renders a confirm modal delete', async () => {
  await act(async () => {
    renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={jest.fn()} attributeCode="foo" />);
  });

  expect(dependencies.translate).toHaveBeenCalledWith(
    'pim_enrich.entity.attribute.module.delete.product_count',
    {count: '3'},
    3
  );
  expect(dependencies.translate).toHaveBeenCalledWith(
    'pim_enrich.entity.attribute.module.delete.product_model_count',
    {count: '5'},
    5
  );
  expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
});

test('it does not allow confirmation until the attributeCodeConfirm field is valid', async () => {
  const onSuccess = jest.fn();

  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={onSuccess} attributeCode="nice_attribute" />);

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.delete'));
  });

  expect(screen.getByText('pim_common.delete')).toHaveAttribute('disabled');
  expect(onSuccess).not.toHaveBeenCalled();

  const input = screen.getByLabelText('pim_enrich.entity.attribute.module.delete.type') as HTMLInputElement;
  fireEvent.change(input, {target: {value: 'nice_attribute'}});

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.delete'));
  });

  expect(global.fetch).toHaveBeenCalledWith('pim_enrich_attribute_rest_remove', {
    method: 'DELETE',
    headers: new Headers({'X-Requested-With': 'XMLHttpRequest'}),
  });
  expect(onSuccess).toHaveBeenCalled();
  expect(dependencies.notify).toHaveBeenCalledWith('success', 'pim_enrich.entity.attribute.flash.delete.success');
});

test('it is submitable using the Enter key (when the confirm is valid)', async () => {
  const onSuccess = jest.fn();

  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={onSuccess} attributeCode="nice_attribute" />);

  const input = screen.getByLabelText('pim_enrich.entity.attribute.module.delete.type') as HTMLInputElement;

  await act(async () => {
    fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  });

  expect(screen.getByText('pim_common.delete')).toHaveAttribute('disabled');
  expect(onSuccess).not.toHaveBeenCalled();

  fireEvent.change(input, {target: {value: 'nice_attribute'}});

  await act(async () => {
    fireEvent.keyDown(input, {key: 'Enter', code: 'Enter'});
  });

  expect(global.fetch).toHaveBeenCalledWith('pim_enrich_attribute_rest_remove', {
    method: 'DELETE',
    headers: new Headers({'X-Requested-With': 'XMLHttpRequest'}),
  });
  expect(onSuccess).toHaveBeenCalled();
  expect(dependencies.notify).toHaveBeenCalledWith('success', 'pim_enrich.entity.attribute.flash.delete.success');
});

test('it displays an error when the delete failed', async () => {
  global.fetch.mockImplementation(async (url: string) => {
    switch (url) {
      case 'pim_enrich_count_items_with_attribute_value':
        return {ok: true, json: () => ({products: 3, product_models: 5})};
      case 'pim_enrich_attribute_rest_remove':
      default:
        return {ok: false, json: () => ({message: 'an_error'})};
    }
  });

  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={jest.fn()} attributeCode="nice_attribute" />);

  const input = screen.getByLabelText('pim_enrich.entity.attribute.module.delete.type') as HTMLInputElement;
  fireEvent.change(input, {target: {value: 'nice_attribute'}});

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.delete'));
  });

  expect(global.fetch).toHaveBeenCalledWith('pim_enrich_attribute_rest_remove', {
    method: 'DELETE',
    headers: new Headers({'X-Requested-With': 'XMLHttpRequest'}),
  });
  expect(dependencies.notify).toHaveBeenCalledWith('error', 'an_error');
});

test('it displays an error when the delete was rejected', async () => {
  global.fetch.mockImplementation(async (url: string) => {
    switch (url) {
      case 'pim_enrich_count_items_with_attribute_value':
        return {ok: true, json: () => ({products: 3, product_models: 5})};
      case 'pim_enrich_attribute_rest_remove':
      default:
        return Promise.reject();
    }
  });

  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={jest.fn()} attributeCode="nice_attribute" />);

  const input = screen.getByLabelText('pim_enrich.entity.attribute.module.delete.type') as HTMLInputElement;
  fireEvent.change(input, {target: {value: 'nice_attribute'}});

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.delete'));
  });

  expect(global.fetch).toHaveBeenCalledWith('pim_enrich_attribute_rest_remove', {
    method: 'DELETE',
    headers: new Headers({'X-Requested-With': 'XMLHttpRequest'}),
  });
  expect(dependencies.notify).toHaveBeenCalledWith('error', 'pim_enrich.entity.attribute.flash.delete.fail');
});
