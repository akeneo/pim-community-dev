import React from 'react';
import {mockedDependencies, renderWithProviders} from '../../tests';
import {fireEvent, screen} from '@testing-library/react';
import {PimNavigation} from './PimNavigation';
import {aMainNavigation} from './navigationHelper';

jest.mock('../PimView');

test('It displays the main navigation with an active entry and a sub menu', async() => {
  renderWithProviders(
    <PimNavigation entries={aMainNavigation()} activeEntryCode='entry1' activeSubEntryCode='subentry2'/>
  );

  expect(screen.getByLabelText('Main navigation')).toBeInTheDocument();
  expect(screen.getByTestId('pim-sub-menu')).toBeInTheDocument();
  expect(screen.queryAllByTestId('pim-main-menu-item')).toHaveLength(3);
  expect(screen.queryAllByTestId('locked-entry')).toHaveLength(0);

});

test('It displays the main navigation with an active entry without a sub menu', async() => {
  renderWithProviders(
    <PimNavigation entries={aMainNavigation()} activeEntryCode='entry2' activeSubEntryCode={null}/>
  );

  expect(screen.queryAllByTestId('pim-main-menu-item')).toHaveLength(3);
  expect(screen.getByLabelText('Main navigation')).toBeInTheDocument();
  expect(screen.queryByTestId('pim-sub-menu')).toBeFalsy();
});

test('It redirects the user to the clicked entry route', async() => {
  renderWithProviders(
    <PimNavigation entries={aMainNavigation()} activeEntryCode='entry1' activeSubEntryCode='subentry2'/>
  );

  mockedDependencies.router.redirect = jest.fn();
  fireEvent.click(screen.getByText('Entry 1'));
  expect(mockedDependencies.router.redirect).toHaveBeenCalledWith('entry1_route');
});

test('It show disabled entries as locked for the Free Trial', async() => {
  renderWithProviders(
    <PimNavigation entries={aMainNavigation()} activeEntryCode='entry1' activeSubEntryCode='subentry2' freeTrialEnabled={true}/>
  );

  expect(screen.queryAllByTestId('pim-main-menu-item')).toHaveLength(3);
  expect(screen.queryAllByTestId('locked-entry')).toHaveLength(1);
});
