import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AttributeGroupList} from '@akeneo-pim-community/settings-ui';
import {screen} from '@testing-library/react';

let mockedGrantedAcl: string[] = [];
jest.mock('@akeneo-pim-community/shared/lib/hooks/useSecurity', () => ({
  useSecurity: () => ({
    isGranted: (acl: string) => mockedGrantedAcl.includes(acl),
  }),
}));

beforeEach(() => {
  mockedGrantedAcl = ['pim_enrich_attributegroup_sort', 'pim_enrich_attributegroup_mass_delete'];
});

const attributeGroups = [
  {
    code: 'technical',
    attribute_count: 10,
    sort_order: 0,
    labels: {
      en_US: 'Technical',
    },
    is_dqi_activated: false,
  },
  {
    code: 'marketing',
    attribute_count: 2,
    sort_order: 0,
    labels: {
      en_US: 'Marketing',
    },
    is_dqi_activated: true,
  },
];

test('it renders attribute groups', () => {
  renderWithProviders(
    <AttributeGroupList
      attributeGroups={attributeGroups}
      filteredAttributeGroups={attributeGroups}
      isItemSelected={() => false}
      onReorder={jest.fn()}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.getByText('Technical')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.attribute_group.disabled')).toBeInTheDocument();
  expect(screen.getByText('10')).toBeInTheDocument();

  expect(screen.getByText('Marketing')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.attribute_group.activated')).toBeInTheDocument();
  expect(screen.getByText('2')).toBeInTheDocument();

  expect(screen.getAllByRole('checkbox', {hidden: true})).toHaveLength(2);
  expect(screen.getAllByTestId('dragAndDrop')).toHaveLength(2);
});

test('it display a placeholder when there is no attribute groups', () => {
  renderWithProviders(
    <AttributeGroupList
      attributeGroups={attributeGroups}
      filteredAttributeGroups={[]}
      isItemSelected={() => false}
      onReorder={jest.fn()}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.getByText('pim_common.no_search_result')).toBeInTheDocument();
});

test('it hide row select if user cannot mass delete attribute group', () => {
  mockedGrantedAcl = ['pim_enrich_attributegroup_sort'];
  renderWithProviders(
    <AttributeGroupList
      attributeGroups={attributeGroups}
      filteredAttributeGroups={attributeGroups}
      isItemSelected={() => false}
      onReorder={jest.fn()}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.queryByRole('checkbox', {hidden: true})).not.toBeInTheDocument();
  expect(screen.getAllByTestId('dragAndDrop')).toHaveLength(2);
});

test('it hide drag and drop if user cannot drag and drop attribute group', () => {
  mockedGrantedAcl = ['pim_enrich_attributegroup_mass_delete'];
  renderWithProviders(
    <AttributeGroupList
      attributeGroups={attributeGroups}
      filteredAttributeGroups={attributeGroups}
      isItemSelected={() => false}
      onReorder={jest.fn()}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.queryAllByTestId('dragAndDrop')).toHaveLength(0);
  expect(screen.getAllByRole('checkbox', {hidden: true})).toHaveLength(2);
});
