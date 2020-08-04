import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, render} from '@testing-library/react';
import {QuickExportConfigurator} from '../../../Resources/public/js/datagrid/quickexport/component/QuickExportConfigurator';

test('it displays a button and no modal initially', () => {
  const onActionLaunch = jest.fn();
  const getProductCount = jest.fn(() => 3);

  const {getByTitle, queryByTitle} = render(
    <QuickExportConfigurator onActionLaunch={onActionLaunch} getProductCount={getProductCount} />
  );

  expect(getByTitle('pim_datagrid.mass_action_group.quick_export.label')).toBeInTheDocument();
  expect(queryByTitle('pim_common.export')).not.toBeInTheDocument();
});

test('it does not call the action launch if an option is not set', () => {
  const onActionLaunch = jest.fn();
  const getProductCount = jest.fn(() => 3);

  const {getByTitle} = render(
    <QuickExportConfigurator onActionLaunch={onActionLaunch} getProductCount={getProductCount} />
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
    <QuickExportConfigurator onActionLaunch={onActionLaunch} getProductCount={getProductCount} />
  );

  fireEvent.click(getByTitle('pim_datagrid.mass_action_group.quick_export.label'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.csv'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.grid_context'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.with_labels'));
  fireEvent.click(getByTitle('pim_common.export'));

  expect(onActionLaunch).toHaveBeenCalledWith('quick_export_grid_context_csv');

  fireEvent.click(getByTitle('pim_datagrid.mass_action_group.quick_export.label'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.xlsx'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.all_attributes'));
  fireEvent.click(getByText('pim_datagrid.mass_action.quick_export.configurator.with_codes'));
  fireEvent.click(getByTitle('pim_common.export'));

  expect(onActionLaunch).toHaveBeenCalledWith('quick_export_xlsx');
});
