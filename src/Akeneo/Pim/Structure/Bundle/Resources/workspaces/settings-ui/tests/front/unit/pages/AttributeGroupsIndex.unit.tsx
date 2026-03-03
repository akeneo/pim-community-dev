import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {AttributeGroup} from '@akeneo-pim-community/settings-ui/src/models';
import {AttributeGroupsIndex} from '@akeneo-pim-community/settings-ui';
import {fireEvent, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

jest.mock('@akeneo-pim-community/shared/lib/components/PimView', () => ({
  PimView: () => <></>,
}));

const mockedAttributeGroups: AttributeGroup[] = [
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
    sort_order: 1,
    labels: {
      en_US: 'Marketing',
    },
    is_dqi_activated: true,
  },
];

const reorderAttributeGroups = jest.fn();
jest.mock('@akeneo-pim-community/settings-ui/src/hooks/attribute-groups/useAttributeGroups', () => ({
  useAttributeGroups: () => [mockedAttributeGroups, reorderAttributeGroups, false],
}));

test('it renders the attribute group datagrid', () => {
  renderWithProviders(<AttributeGroupsIndex />);

  expect(screen.getByText('Technical')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.attribute_group.disabled')).toBeInTheDocument();
  expect(screen.getByText('10')).toBeInTheDocument();
});

test('it can reorder attribute groups with drag and drop', () => {
  renderWithProviders(<AttributeGroupsIndex />);

  let dataTransferred = '';
  const dataTransfer = {
    getData: (_format: string) => dataTransferred,
    setData: (_format: string, data: string) => (dataTransferred = data),
  };

  fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[0]);
  fireEvent.dragStart(screen.getAllByRole('row')[1], {dataTransfer});
  fireEvent.dragEnter(screen.getAllByRole('row')[2], {dataTransfer});
  fireEvent.dragLeave(screen.getAllByRole('row')[2], {dataTransfer});
  fireEvent.drop(screen.getAllByRole('row')[2], {dataTransfer});
  fireEvent.dragEnd(screen.getAllByRole('row')[1], {dataTransfer});

  expect(reorderAttributeGroups).toBeCalledWith([1, 0]);
});

test('it can filter attribute groups', () => {
  renderWithProviders(<AttributeGroupsIndex />);

  expect(screen.getByText('Technical')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.attribute_group.disabled')).toBeInTheDocument();
  expect(screen.getByText('10')).toBeInTheDocument();

  expect(screen.getByText('Marketing')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.attribute_group.activated')).toBeInTheDocument();
  expect(screen.getByText('2')).toBeInTheDocument();

  const searchInput = screen.getByPlaceholderText('pim_common.search');
  userEvent.type(searchInput, 'te');

  expect(screen.getByText('Technical')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.attribute_group.disabled')).toBeInTheDocument();
  expect(screen.getByText('10')).toBeInTheDocument();

  expect(screen.queryByText('Marketing')).not.toBeInTheDocument();
  expect(screen.queryByText('akeneo_data_quality_insights.attribute_group.activated')).not.toBeInTheDocument();
  expect(screen.queryByText('2')).not.toBeInTheDocument();
});

test('it display mass action when user select an attribute group', () => {
  renderWithProviders(<AttributeGroupsIndex />);

  expect(screen.queryByText('pim_common.delete')).not.toBeInTheDocument();

  const technicalAttributeGroupCheckBox = screen.getAllByRole('checkbox')[0];
  userEvent.click(technicalAttributeGroupCheckBox);

  expect(screen.getByText('pim_common.delete')).toBeInTheDocument();
});

test('it remove mass action when user unselect all attribute groups from toolbar', () => {
  renderWithProviders(<AttributeGroupsIndex />);

  const technicalAttributeGroupCheckBox = screen.getAllByRole('checkbox')[0];
  userEvent.click(technicalAttributeGroupCheckBox);

  expect(screen.getByText('pim_common.delete')).toBeInTheDocument();

  userEvent.click(screen.getByTitle('pim_enrich.entity.attribute_group.dropdown.label'));
  userEvent.click(screen.getByTitle('pim_enrich.entity.attribute_group.dropdown.none'));

  expect(screen.queryByText('pim_common.delete')).not.toBeInTheDocument();
});

test('it select all attribute groups when user select all attribute groups from toolbar', () => {
  renderWithProviders(<AttributeGroupsIndex />);

  const technicalAttributeGroupCheckBox = screen.getAllByRole('checkbox')[0];
  userEvent.click(technicalAttributeGroupCheckBox);
  userEvent.click(screen.getByTitle('pim_enrich.entity.attribute_group.dropdown.label'));
  userEvent.click(screen.getByTitle('pim_enrich.entity.attribute_group.dropdown.all'));

  expect(screen.getAllByRole('checkbox')[0]).toBeChecked();
  expect(screen.getAllByRole('checkbox')[1]).toBeChecked();

  expect(screen.queryByText('pim_common.delete')).toBeInTheDocument();
});
