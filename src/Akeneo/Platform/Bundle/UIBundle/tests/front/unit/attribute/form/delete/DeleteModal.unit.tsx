import '@testing-library/jest-dom';
import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
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

const flushPromises = () => new Promise(setImmediate);

jest.mock('@akeneo-pim-community/legacy-bridge/src/provider/dependencies');

test('it renders a confirm modal delete', () => {
  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={jest.fn()} deleteUrl="fake_delete_url" />);

  expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
});

test('it calls the attribute remover when confirm is clicked', async () => {
  global.fetch.mockImplementationOnce(() =>
    Promise.resolve({
      ok: true,
    })
  );

  const onSuccess = jest.fn();

  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={onSuccess} deleteUrl="fake_delete_url" />);

  const confirmDeleteButton = screen.getByText('pim_common.delete');
  fireEvent.click(confirmDeleteButton);

  await flushPromises();

  expect(global.fetch).toHaveBeenCalledWith('fake_delete_url', {
    method: 'DELETE',
    headers: new Headers({'X-Requested-With': 'XMLHttpRequest'}),
  });
  expect(onSuccess).toHaveBeenCalled();
  expect(dependencies.notify).toHaveBeenCalledWith('success', 'pim_enrich.entity.attribute.flash.delete.success');
});

test('it displays an error when the delete failed', async () => {
  global.fetch.mockImplementationOnce(() => Promise.reject({}));

  renderWithProviders(<DeleteModal onCancel={jest.fn()} onSuccess={jest.fn()} deleteUrl="fake_delete_url" />);

  const confirmDeleteButton = screen.getByText('pim_common.delete');
  fireEvent.click(confirmDeleteButton);

  await flushPromises();

  expect(global.fetch).toHaveBeenCalledWith('fake_delete_url', {
    method: 'DELETE',
    headers: new Headers({'X-Requested-With': 'XMLHttpRequest'}),
  });
  expect(dependencies.notify).toHaveBeenCalledWith('error', 'pim_enrich.entity.attribute.flash.delete.fail');
});
