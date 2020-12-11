import '@testing-library/jest-dom';
import React from 'react';
import {fireEvent, screen, getByText, act} from '@testing-library/react';
import {DeleteAction} from 'pimui/js/attribute/form/delete/DeleteAction';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {dependencies} from '@akeneo-pim-community/legacy-bridge/src/provider/dependencies';

const flushPromises = () =>
  act(async () => {
    await new Promise(setImmediate);
  });

test('it renders a delete action button', () => {
  renderWithProviders(<DeleteAction attributeCode={'foo'} />);

  expect(screen.getByText('pim_common.delete')).toBeInTheDocument();
});

test('it opens the confirm modal on click', () => {
  renderWithProviders(<DeleteAction attributeCode={'foo'} />);

  const openModalButton = screen.getByText('pim_common.delete');
  fireEvent.click(openModalButton);

  expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
});

test('it redirect to the list after attribute is deleted', async () => {
  renderWithProviders(<DeleteAction attributeCode={'foo'} />);

  const openModalButton = screen.getByText('pim_common.delete');
  fireEvent.click(openModalButton);

  const modal = screen.getByRole('dialog');

  const confirmDeleteButton = getByText(modal, 'pim_common.delete');
  fireEvent.click(confirmDeleteButton);

  await flushPromises();

  expect(dependencies.router.redirect).toHaveBeenCalledWith('pim_enrich_attribute_index');
});
