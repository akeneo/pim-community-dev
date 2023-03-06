import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {MassDeleteAttributeGroupsModal} from '../../../../../../../src';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

test('it renders a confirm modal delete button', () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal selectedCount={0} impactedAttributesCount={0} availableTargetAttributeGroups={[]} />
  );

  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.button')).toBeInTheDocument();
  expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
});

test('it opens a modal with a confirmation input & helper if there are child attributes', () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      selectedCount={1}
      impactedAttributesCount={3}
      availableTargetAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 2},
      ]}
    />
  );

  userEvent.click(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.button'));

  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.confirm')).toBeInTheDocument();
  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.attribute_warning')).toBeInTheDocument();
  expect(
    screen.getByLabelText('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase')
  ).toBeInTheDocument();
});

test('it can select an attribute group to assign affected attributes', () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      selectedCount={1}
      impactedAttributesCount={3}
      availableTargetAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 4},
        {code: 'attribute_group_2', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 5},
      ]}
    />
  );

  userEvent.click(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.button'));

  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group')).toBeInTheDocument();
  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.confirm')).toBeInTheDocument();

  userEvent.click(screen.getByLabelText('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group'));

  expect(screen.getByText('[attribute_group_1]')).toBeInTheDocument();
  expect(screen.getByText('[attribute_group_2]')).toBeInTheDocument();

  userEvent.click(screen.getByText('[attribute_group_1]'));
});

test('it can close the modal when confirming with the correct word', () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      selectedCount={1}
      impactedAttributesCount={3}
      availableTargetAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 4},
        {code: 'attribute_group_2', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 5},
      ]}
    />
  );

  userEvent.click(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.button'));
  expect(screen.getByRole('dialog')).toBeInTheDocument();

  userEvent.type(
    screen.getByLabelText('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase'),
    'pim_enrich.entity.attribute_group.mass_delete.confirmation_word'
  );
  userEvent.click(screen.getByText('pim_common.delete'));

  expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
});
