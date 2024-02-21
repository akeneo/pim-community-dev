import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DeleteAttributeGroupModal} from '@akeneo-pim-community/settings-ui';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

const deleteAttributeGroup = jest.fn();
jest.mock('@akeneo-pim-community/settings-ui/src/hooks/attribute-groups/useDeleteAttributeGroup', () => ({
  useDeleteAttributeGroup: () => [false, deleteAttributeGroup],
}));
jest.mock('@akeneo-pim-community/settings-ui/src/hooks/attribute-groups/useAttributeGroups', () => ({
  useAttributeGroups: () => [
    [
      {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 3},
      {code: 'attribute_group_2', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 6},
      {code: 'attribute_group_3', labels: {}, sort_order: 3, is_dqi_activated: false, attribute_count: 9},
    ],
    jest.fn(),
    false,
  ],
}));

beforeEach(() => {
  deleteAttributeGroup.mockReset();
});

test('it renders a modal with helper if there are child attributes', () => {
  renderWithProviders(
    <DeleteAttributeGroupModal attributeGroupCode="attribute_group_1" isOpen={true} onClose={jest.fn()} />
  );

  expect(screen.getByText('pim_enrich.entity.attribute_group.delete.confirm')).toBeInTheDocument();
  expect(screen.getByText('pim_enrich.entity.attribute_group.delete.attribute_warning')).toBeInTheDocument();
});

test('it can select an attribute group to assign affected attributes', () => {
  renderWithProviders(
    <DeleteAttributeGroupModal attributeGroupCode="attribute_group_1" isOpen={true} onClose={jest.fn()} />
  );

  expect(screen.getByText('pim_enrich.entity.attribute_group.delete.select_attribute_group')).toBeInTheDocument();
  expect(screen.getByText('pim_enrich.entity.attribute_group.delete.confirm')).toBeInTheDocument();

  userEvent.click(screen.getByLabelText('pim_enrich.entity.attribute_group.delete.select_attribute_group'));

  expect(screen.getByText('[attribute_group_2]')).toBeInTheDocument();
  expect(screen.getByText('[attribute_group_3]')).toBeInTheDocument();

  userEvent.click(screen.getByText('[attribute_group_3]'));
});

test('it launches delete', async () => {
  const onClose = jest.fn();
  renderWithProviders(
    <DeleteAttributeGroupModal attributeGroupCode="attribute_group_1" isOpen={true} onClose={onClose} />
  );

  userEvent.click(screen.getByLabelText('pim_enrich.entity.attribute_group.delete.select_attribute_group'));
  userEvent.click(screen.getByText('[attribute_group_2]'));

  await act(async () => {
    await userEvent.click(screen.getByText('pim_common.delete'));
  });
  expect(deleteAttributeGroup).toBeCalled();
  expect(onClose).toBeCalled();
});

test('it close the modal when user clicks on cancel', async () => {
  const onClose = jest.fn();
  renderWithProviders(
    <DeleteAttributeGroupModal attributeGroupCode="attribute_group_1" isOpen={true} onClose={onClose} />
  );

  await act(async () => {
    await userEvent.click(screen.getByText('pim_common.cancel'));
  });
  expect(onClose).toBeCalled();
});
