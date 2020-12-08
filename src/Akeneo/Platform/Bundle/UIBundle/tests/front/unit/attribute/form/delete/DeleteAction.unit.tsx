import '@testing-library/jest-dom';
import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {DeleteAction} from 'pimui/js/attribute/form/delete/DeleteAction';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

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
