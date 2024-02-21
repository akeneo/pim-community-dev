import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AttributeGroupSecondaryActions, DeleteAttributeGroupModalProps} from '@akeneo-pim-community/settings-ui';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

jest.mock('@akeneo-pim-community/settings-ui/src/components/DeleteAttributeGroupModal', () => ({
  DeleteAttributeGroupModal: ({attributeGroupCode, isOpen}: DeleteAttributeGroupModalProps) =>
    isOpen && <div>Delete {attributeGroupCode}</div>,
}));

test('it renders a delete button', () => {
  renderWithProviders(<AttributeGroupSecondaryActions attributeGroupCode="attribute_group_1" />);

  userEvent.click(screen.getByTitle('pim_common.other_actions'));

  expect(screen.getByText('pim_common.delete')).toBeInTheDocument();
  expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
});

test('it renders nothing if the attribute group code matches locked attribute group code', () => {
  renderWithProviders(<AttributeGroupSecondaryActions attributeGroupCode="other" />);

  expect(screen.queryByTitle('pim_common.other_actions')).not.toBeInTheDocument();
  expect(screen.queryByText('pim_common.delete')).not.toBeInTheDocument();
});

test('it opens a modal', () => {
  renderWithProviders(<AttributeGroupSecondaryActions attributeGroupCode="attribute_group_1" />);

  userEvent.click(screen.getByTitle('pim_common.other_actions'));
  userEvent.click(screen.getByText('pim_common.delete'));

  expect(screen.getByText('Delete attribute_group_1')).toBeInTheDocument();
});
