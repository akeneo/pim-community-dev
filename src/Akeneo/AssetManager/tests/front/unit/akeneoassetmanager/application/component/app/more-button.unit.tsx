import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {MoreButton} from 'akeneoassetmanager/application/component/app/more-button';

const actions = [
  {
    label: 'my nice action',
    action: () => {},
  },
  {
    label: 'my second nice action',
    action: () => {},
  },
];
test('It should a closed more button', () => {
  renderWithProviders(<MoreButton elements={actions} />);

  expect(screen.getByTitle('pim_asset_manager.asset_collection.open_other_actions')).toBeInTheDocument();
  expect(screen.queryByText('pim_asset_manager.asset_collection.other_actions')).toBeNull();
});

test('It should open a more button by clicking it', () => {
  renderWithProviders(<MoreButton elements={actions} />);
  expect(screen.queryByText('pim_asset_manager.asset_collection.other_actions')).toBeNull();

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_collection.open_other_actions'));

  expect(screen.getByText('pim_asset_manager.asset_collection.other_actions')).toBeInTheDocument();
  expect(screen.getByText('my nice action')).toBeInTheDocument();
  expect(screen.getByText('my second nice action')).toBeInTheDocument();
  expect(screen.getByText('pim_asset_manager.asset_collection.dismiss_other_actions')).toBeInTheDocument();
});

test('It should dismiss more button', () => {
  renderWithProviders(<MoreButton elements={actions} />);

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_collection.open_other_actions'));

  expect(screen.getByText('pim_asset_manager.asset_collection.other_actions')).toBeInTheDocument();

  fireEvent.click(screen.getByText('pim_asset_manager.asset_collection.dismiss_other_actions'));

  expect(screen.queryByText('pim_asset_manager.asset_collection.other_actions')).toBeNull();
});

test('It should apply an action on a more button', () => {
  let valueToUpdate = 2;
  renderWithProviders(
    <MoreButton
      elements={[
        {
          label: 'my nice action',
          action: () => {
            valueToUpdate = 3;
          },
        },
      ]}
    />
  );

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_collection.open_other_actions'));
  fireEvent.click(screen.getByText('my nice action'));
  expect(valueToUpdate).toEqual(3);
});

test('It should apply an action on a more button', () => {
  let valueToUpdate = 2;
  renderWithProviders(
    <MoreButton
      elements={[
        {
          label: 'my nice action',
          action: () => {
            valueToUpdate = 3;
          },
        },
      ]}
    />
  );

  fireEvent.click(screen.getByTitle('pim_asset_manager.asset_collection.open_other_actions'));
  fireEvent.keyPress(screen.getByText('my nice action'), {key: ' ', keyCode: 32, charCode: 32});
  expect(valueToUpdate).toEqual(3);
});
