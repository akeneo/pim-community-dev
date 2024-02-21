import React, {ReactElement} from 'react';
import {Table} from 'akeneo-design-system';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AttributeGroupRow} from '@akeneo-pim-community/settings-ui';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

const mockRedirect = jest.fn();
jest.mock('@akeneo-pim-community/shared/lib/hooks/useRouter', () => ({
  useRouter: () => ({
    generate: (route: string) => route,
    redirect: mockRedirect,
  }),
}));

let mockedGrantedAcl: string[] = [];
jest.mock('@akeneo-pim-community/shared/lib/hooks/useSecurity', () => ({
  useSecurity: () => ({
    isGranted: (acl: string) => mockedGrantedAcl.includes(acl),
  }),
}));

beforeEach(() => {
  mockRedirect.mockClear();
  mockedGrantedAcl = ['pim_enrich_attributegroup_edit'];
});

const attributeGroup = {
  code: 'technical',
  attribute_count: 10,
  sort_order: 0,
  labels: {
    en_US: 'Technical',
  },
  is_dqi_activated: false,
};

const renderAttributeGroupRow = (row: ReactElement) =>
  renderWithProviders(
    <Table isSelectable={true}>
      <Table.Body>{row}</Table.Body>
    </Table>
  );

test('it renders a attribute group row', () => {
  renderAttributeGroupRow(
    <AttributeGroupRow attributeGroup={attributeGroup} isSelected={false} onSelectionChange={jest.fn()} />
  );

  expect(screen.getByText('Technical')).toBeInTheDocument();
  expect(screen.getByText('10')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.attribute_group.disabled')).toBeInTheDocument();
});

test('it display the code if the attribute group have no label', () => {
  const attributeGroup = {
    code: 'technical',
    attribute_count: 10,
    sort_order: 0,
    labels: {},
    is_dqi_activated: false,
  };

  renderAttributeGroupRow(
    <AttributeGroupRow attributeGroup={attributeGroup} isSelected={false} onSelectionChange={jest.fn()} />
  );

  expect(screen.getByText('[technical]')).toBeInTheDocument();
});

test('it redirect user when user click on row', () => {
  renderAttributeGroupRow(
    <AttributeGroupRow attributeGroup={attributeGroup} isSelected={false} onSelectionChange={jest.fn()} />
  );

  userEvent.click(screen.getByText('Technical'));
  expect(mockRedirect).toHaveBeenCalled();
});

test('it did not redirect user when user click on row without edit acl', () => {
  mockedGrantedAcl = [];
  renderAttributeGroupRow(
    <AttributeGroupRow attributeGroup={attributeGroup} isSelected={false} onSelectionChange={jest.fn()} />
  );

  userEvent.click(screen.getByText('Technical'));
  expect(mockRedirect).not.toHaveBeenCalled();
});

test('it allow user to select attribute group', () => {
  const handleSelectionChange = jest.fn();
  renderAttributeGroupRow(
    <AttributeGroupRow attributeGroup={attributeGroup} isSelected={false} onSelectionChange={handleSelectionChange} />
  );

  userEvent.click(screen.getByRole('checkbox', {hidden: true}));

  expect(handleSelectionChange).toHaveBeenCalledWith(attributeGroup, true);
});
