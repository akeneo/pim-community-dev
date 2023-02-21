import '@testing-library/jest-dom';
import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {MassDeleteAttributeGroupsModal} from '../../../../../../../src';
import {fireEvent, screen, act} from '@testing-library/react';

test('it renders a confirm modal delete', () => {
  renderWithProviders(<MassDeleteAttributeGroupsModal selectedAttributeGroups={[]} unselectAttributeGroups={[]} onConfirm={() => {}} />);

  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.button')).toBeInTheDocument();
});

test('it display number of attribute groups to delete', async () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      selectedAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 0},
      ]}
      unselectAttributeGroups={[]}
      onConfirm={() => {}}
      onConfirm={() => {}}
    />
  );

  await act(async () => {
    fireEvent.click(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.button'));
  });

  expect(
    screen.queryByText('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group')
  ).not.toBeInTheDocument();
  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.confirm')).toBeInTheDocument();
});

test('it display number of attribute affected', async () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      selectedAttributeGroups={[
        {code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false, attribute_count: 4},
        {code: 'attribute_group_2', labels: {}, sort_order: 2, is_dqi_activated: false, attribute_count: 5},
      ]}
      unselectAttributeGroups={[]}
      onConfirm={() => {}}
    />
  );

  await act(async () => {
    fireEvent.click(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.button'));
  });

  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group')).toBeInTheDocument();
  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.confirm')).toBeInTheDocument();
});
