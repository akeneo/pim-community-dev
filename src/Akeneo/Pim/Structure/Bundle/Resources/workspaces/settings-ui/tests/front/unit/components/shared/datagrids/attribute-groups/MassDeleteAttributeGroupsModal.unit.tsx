import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {MassDeleteAttributeGroupsModal} from '@akeneo-pim-community/settings-ui';
import {fireEvent, screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

const launchMassDelete = jest.fn();
jest.mock('@akeneo-pim-community/settings-ui/src/hooks/attribute-groups/useMassDeleteAttributeGroups', () => ({
  useMassDeleteAttributeGroups: () => [false, launchMassDelete],
}));

beforeEach(() => {
  launchMassDelete.mockReset();
});

test('it renders a confirm modal delete button', () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal impactedAttributeGroups={[]} availableReplacementAttributeGroups={[]} />
  );

  expect(screen.getByText('pim_common.delete')).toBeInTheDocument();
  expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
});

test('it opens a modal with a confirmation input & helper if there are child attributes', () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      impactedAttributeGroups={[
        {code: 'attribute_group_3', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 3},
      ]}
      availableReplacementAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 2},
      ]}
    />
  );

  userEvent.click(screen.getByText('pim_common.delete'));

  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.confirm')).toBeInTheDocument();
  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.attribute_warning')).toBeInTheDocument();
  expect(
    screen.getByLabelText('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase')
  ).toBeInTheDocument();
});

test('it can select an attribute group to assign affected attributes', () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      impactedAttributeGroups={[
        {code: 'attribute_group_3', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 3},
      ]}
      availableReplacementAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 4},
        {code: 'attribute_group_2', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 5},
      ]}
    />
  );

  userEvent.click(screen.getByText('pim_common.delete'));

  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group')).toBeInTheDocument();
  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.confirm')).toBeInTheDocument();

  userEvent.click(screen.getByLabelText('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group'));

  expect(screen.getByText('[attribute_group_1]')).toBeInTheDocument();
  expect(screen.getByText('[attribute_group_2]')).toBeInTheDocument();

  userEvent.click(screen.getByText('[attribute_group_1]'));
});

test('it can close the modal when confirming with the correct word', async () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      impactedAttributeGroups={[
        {code: 'attribute_group_3', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 0},
      ]}
      availableReplacementAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 4},
        {code: 'attribute_group_2', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 5},
      ]}
    />
  );

  userEvent.click(screen.getAllByText('pim_common.delete')[0]);
  expect(screen.getByRole('dialog')).toBeInTheDocument();

  userEvent.type(
    screen.getByLabelText('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase'),
    'pim_enrich.entity.attribute_group.mass_delete.confirmation_word'
  );

  await act(async () => {
    await userEvent.click(screen.getAllByText('pim_common.delete')[1]);
  });

  expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
});

test('it cannot mass delete if no target attribute group is selected', async () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      impactedAttributeGroups={[
        {code: 'attribute_group_3', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 3},
      ]}
      availableReplacementAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 4},
        {code: 'attribute_group_2', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 5},
      ]}
    />
  );

  fireEvent.click(screen.getAllByText('pim_common.delete')[0]);

  await act(async () => {
    await userEvent.click(screen.getAllByText('pim_common.delete')[1]);
  });

  expect(launchMassDelete).not.toBeCalled();
});

test('it launch mass delete', async () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      impactedAttributeGroups={[
        {code: 'attribute_group_3', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 3},
      ]}
      availableReplacementAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 4},
        {
          code: 'attribute_group_3',
          labels: {en_US: 'attribute group 3'},
          sort_order: 2,
          is_dqi_activated: false,
          attribute_count: 5,
        },
      ]}
    />
  );

  fireEvent.click(screen.getAllByText('pim_common.delete')[0]);
  fireEvent.click(screen.getByLabelText('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group'));
  fireEvent.click(screen.getByText('attribute group 3'));
  userEvent.type(
    screen.getByLabelText('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase'),
    'pim_enrich.entity.attribute_group.mass_delete.confirmation_word'
  );

  await act(async () => {
    await userEvent.click(screen.getAllByText('pim_common.delete')[1]);
  });
  expect(launchMassDelete).toBeCalled();
});

test('it resets assigned attribute group when modal is closed', () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      impactedAttributeGroups={[
        {code: 'attribute_group_3', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 3},
      ]}
      availableReplacementAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 4},
        {code: 'attribute_group_2', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 5},
      ]}
    />
  );

  userEvent.click(screen.getByText('pim_common.delete'));
  userEvent.click(screen.getByLabelText('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group'));
  userEvent.click(screen.getByText('[attribute_group_1]'));
  expect(screen.getByText('[attribute_group_1]')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.cancel'));
  userEvent.click(screen.getByText('pim_common.delete'));

  expect(screen.queryByText('[attribute_group_1]')).not.toBeInTheDocument();
});
