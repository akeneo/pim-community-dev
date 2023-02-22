import '@testing-library/jest-dom';
import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {MassDeleteAttributeGroupsModal} from '../../../../../../../src';
import {fireEvent, screen, act} from '@testing-library/react';

test('it renders a confirm modal delete', () => {
  renderWithProviders(<MassDeleteAttributeGroupsModal attributeGroups={[]} />);

  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.button')).toBeInTheDocument();
});

test('it display number of attribute groups to delete', async () => {
  renderWithProviders(
    <MassDeleteAttributeGroupsModal
      attributeGroups={[{code: 'attribute_group_1', labels: {}, sort_order: 1, is_dqi_activated: false}]}
    />
  );

  await act(async () => {
    fireEvent.click(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.button'));
  });

  expect(screen.getByText('pim_enrich.entity.attribute_group.mass_delete.confirm')).toBeInTheDocument();
});
