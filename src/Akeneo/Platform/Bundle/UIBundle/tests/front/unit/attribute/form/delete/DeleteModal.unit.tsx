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

beforeAll(() => {
  global.fetch = jest.fn();
});

afterEach(() => {
  global.fetch && global.fetch.mockClear();
});

const flushPromises = () => act(async () => {
    await new Promise(setImmediate);
});

jest.mock('@akeneo-pim-community/legacy-bridge/src/provider/dependencies');

test('it renders a confirm modal delete', () => {
  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={jest.fn()} attributeCode="foo" />);

  expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
});

test('it calls the attribute remover when confirm is clicked', async () => {
  global.fetch.mockImplementationOnce(() =>
    Promise.resolve({
      ok: true,
    })
  );

  const onSuccess = jest.fn();

  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={onSuccess} attributeCode="foo" />);

  const confirmDeleteButton = screen.getByText('pim_common.delete');
  fireEvent.click(confirmDeleteButton);

  await flushPromises();

  expect(global.fetch).toHaveBeenCalledWith('pim_enrich_attribute_rest_remove', {
    method: 'DELETE',
    headers: new Headers({'X-Requested-With': 'XMLHttpRequest'}),
  });
  expect(onSuccess).toHaveBeenCalled();
  expect(dependencies.notify).toHaveBeenCalledWith('success', 'pim_enrich.entity.attribute.flash.delete.success');
});

test('it displays an error when the delete failed', async () => {
  global.fetch.mockImplementationOnce(() =>
    Promise.resolve({
      ok: false,
      json: () => Promise.resolve({
        message: 'an_error',
      }),
    })
  );

  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={jest.fn()} attributeCode="foo" />);

  const confirmDeleteButton = screen.getByText('pim_common.delete');
  fireEvent.click(confirmDeleteButton);

  await flushPromises();

  expect(global.fetch).toHaveBeenCalledWith('pim_enrich_attribute_rest_remove', {
    method: 'DELETE',
    headers: new Headers({'X-Requested-With': 'XMLHttpRequest'}),
  });
  expect(dependencies.notify).toHaveBeenCalledWith('error', 'an_error');
});

test('it displays an error when the delete was rejected', async () => {
  global.fetch.mockImplementationOnce(() => Promise.reject({}));

  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={jest.fn()} attributeCode="foo" />);

  const confirmDeleteButton = screen.getByText('pim_common.delete');
  fireEvent.click(confirmDeleteButton);

  await flushPromises();

  expect(global.fetch).toHaveBeenCalledWith('pim_enrich_attribute_rest_remove', {
    method: 'DELETE',
    headers: new Headers({'X-Requested-With': 'XMLHttpRequest'}),
  });
  expect(dependencies.notify).toHaveBeenCalledWith('error', 'pim_enrich.entity.attribute.flash.delete.fail');
});
