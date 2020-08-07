import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, render} from '@testing-library/react';
import {QuickExportConfigurator} from '../../../Resources/public/js/datagrid/quickexport/component/QuickExportConfigurator';

test('it displays a button and no modal initially', () => {
  const onActionLaunch = jest.fn();
  const getProductCount = jest.fn(() => 3);

  const {getByTitle, queryByTitle} = render(
    <QuickExportConfigurator
      showWithLabelsSelect={true}
      onActionLaunch={onActionLaunch}
      getProductCount={getProductCount}
    />
  );

  expect(getByTitle('pim_datagrid.mass_action_group.quick_export.label')).toBeInTheDocument();
  expect(queryByTitle('pim_common.export')).not.toBeInTheDocument();
});

test('it does not call the action launch if an option is not set', () => {
  const onActionLaunch = jest.fn();
  const getProductCount = jest.fn(() => 3);

  const {getByTitle} = render(
    <QuickExportConfigurator
      showWithLabelsSelect={true}
      onActionLaunch={onActionLaunch}
      getProductCount={getProductCount}
    />
  );

  fireEvent.click(getByTitle('pim_datagrid.mass_action_group.quick_export.label'));

  const confirmButton = getByTitle('pim_common.export');
  fireEvent.click(confirmButton);

  expect(confirmButton).toBeInTheDocument();
  expect(onActionLaunch).not.toHaveBeenCalled();
});

test('it does call the action launch if every option is set', () => {
  const onActionLaunch = jest.fn();
  const getProductCount = jest.fn(() => 3);

  const {getByTitle, getByText} = render(
    <QuickExportConfigurator
      showWithLabelsSelect={true}
      onActionLaunch={onActionLaunch}
      getProductCount={getProductCount}
    />
  );

  fireEvent.click(getByTitle('pim_datagrid.mass_action_group.quick_export.label'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.csv'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.grid_context'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.with_labels'));
  fireEvent.click(getByTitle('pim_common.export'));

  expect(onActionLaunch).toHaveBeenCalledWith({context: 'grid-context', type: 'csv', 'with-labels': 'with-labels'});

  fireEvent.click(getByTitle('pim_datagrid.mass_action_group.quick_export.label'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.xlsx'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.all_attributes'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.with_codes'));
  fireEvent.click(getByTitle('pim_common.export'));

  expect(onActionLaunch).toHaveBeenCalledWith({context: 'all-attributes', type: 'xlsx', 'with-labels': 'with-codes'});
});

test('it does not display the with-labels select if specified', () => {
  const onActionLaunch = jest.fn();
  const getProductCount = jest.fn(() => 3);

  const {queryByText} = render(
    <QuickExportConfigurator
      showWithLabelsSelect={false}
      onActionLaunch={onActionLaunch}
      getProductCount={getProductCount}
    />
  );

  expect(queryByText('pim_datagrid.mass_action.quick_export.configurator.with_labels')).not.toBeInTheDocument();
});
