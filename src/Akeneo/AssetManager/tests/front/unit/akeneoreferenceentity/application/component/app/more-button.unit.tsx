import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent, act} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneopimenrichmentassetmanager/platform/component/theme';
import {MoreButton} from 'akeneoassetmanager/application/component/app/more-button';

const actions = [{
  label: 'my nice action',
  action: () => {}
},{
  label: 'my second nice action',
  action: () => {}
}]
test('It should a closed more button', () => {
  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MoreButton elements={actions}/>
    </ThemeProvider>
  );

  expect(getByText('pim_asset_manager.asset_collection.open_other_actions')).toBeInTheDocument();
  expect(queryByText('pim_asset_manager.asset_collection.other_actions')).toBeNull();
});

test('It should open a more button', () => {
  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MoreButton elements={actions}/>
    </ThemeProvider>
  );
  expect(queryByText('pim_asset_manager.asset_collection.other_actions')).toBeNull();

  fireEvent.click(getByText('pim_asset_manager.asset_collection.open_other_actions'))

  expect(getByText('pim_asset_manager.asset_collection.other_actions')).toBeInTheDocument();
  expect(getByText('my nice action')).toBeInTheDocument();
  expect(getByText('my second nice action')).toBeInTheDocument();
  expect(getByText('pim_asset_manager.asset_collection.dismiss_other_actions')).toBeInTheDocument();
});

test('It should dismiss more button', () => {
  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MoreButton elements={actions}/>
    </ThemeProvider>
  );

  fireEvent.click(getByText('pim_asset_manager.asset_collection.open_other_actions'))

  expect(getByText('pim_asset_manager.asset_collection.other_actions')).toBeInTheDocument();

  fireEvent.click(getByText('pim_asset_manager.asset_collection.dismiss_other_actions'));

  expect(queryByText('pim_asset_manager.asset_collection.other_actions')).toBeNull();
});

test('It should apply an action on a more button', () => {
  let valueToUpdate = 2;
  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <MoreButton elements={[{
      label: 'my nice action',
      action: () => {
        valueToUpdate = 3;
      }
    }]}/>
    </ThemeProvider>
  );

  fireEvent.click(getByText('pim_asset_manager.asset_collection.open_other_actions'))
  fireEvent.click(getByText('my nice action'))
  expect(valueToUpdate).toEqual(3);
});
