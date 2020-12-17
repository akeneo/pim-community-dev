import '@testing-library/jest-dom';
import React from 'react';
import {fireEvent, screen, getByText, act} from '@testing-library/react';
import {DeleteAction} from 'pimui/js/attribute/form/delete/DeleteAction';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {dependencies} from '@akeneo-pim-community/legacy-bridge/src/provider/dependencies';

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

test('it renders a delete action button', () => {
  renderWithProviders(<DeleteAction attributeCode="foo" />);

  expect(screen.getByText('pim_common.delete')).toBeInTheDocument();
});

test('it opens the confirm modal on click', async () => {
  renderWithProviders(<DeleteAction attributeCode="foo" />);

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.delete'));
  });

  expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
});

test('it redirects to the list after attribute is deleted', async () => {
  renderWithProviders(<DeleteAction attributeCode="foo" />);

  await act(async () => {
    fireEvent.click(screen.getByText('pim_common.delete'));
  });

  const modal = screen.getByRole('dialog');

  await act(async () => {
    fireEvent.click(getByText(modal, 'pim_common.delete'));
  });

  expect(dependencies.router.redirect).toHaveBeenCalledWith('pim_enrich_attribute_index');
});
