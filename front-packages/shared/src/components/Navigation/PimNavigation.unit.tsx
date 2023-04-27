import React from 'react';
import {mockedDependencies, renderWithProviders} from '../../tests';
import {fireEvent, screen, waitFor} from '@testing-library/react';
import {PimNavigation} from './PimNavigation';
import {aMainNavigation} from './navigationTestHelper';

jest.mock('../PimView');

beforeAll(() => {
  // @ts-ignore
  global.fetch = () =>
    Promise.resolve({
      ok: true,
      status: 200,
      json: () => Promise.resolve({pim_version: 'serenity'}),
    });
});

test('It displays the main navigation with an active entry and a sub menu', async () => {
  renderWithProviders(
    <PimNavigation entries={aMainNavigation()} activeEntryCode="entry1" activeSubEntryCode="subentry2" />
  );

  expect(screen.getByLabelText('Main navigation')).toBeInTheDocument();
  expect(screen.getByTestId('pim-sub-menu')).toBeInTheDocument();
  expect(screen.queryAllByTestId('pim-main-menu-item')).toHaveLength(3);
  expect(screen.queryAllByTestId('locked-entry')).toHaveLength(0);
});

test('It displays the main navigation with an active entry without a sub menu', async () => {
  renderWithProviders(<PimNavigation entries={aMainNavigation()} activeEntryCode="entry2" activeSubEntryCode={null} />);

  expect(screen.queryAllByTestId('pim-main-menu-item')).toHaveLength(3);
  expect(screen.getByLabelText('Main navigation')).toBeInTheDocument();
  expect(screen.queryByTestId('pim-sub-menu')).toBeFalsy();
});

test('It redirects the user to the clicked entry route', async () => {
  renderWithProviders(
    <PimNavigation entries={aMainNavigation()} activeEntryCode="entry1" activeSubEntryCode="subentry2" />
  );

  fireEvent.click(screen.getByText('Entry 1'));
  expect(mockedDependencies.router?.redirect).toHaveBeenCalledWith('entry1_route');
});

test('It show disabled entries as locked for the Free Trial', async () => {
  renderWithProviders(
    <PimNavigation
      entries={aMainNavigation()}
      activeEntryCode="entry10"
      activeSubEntryCode="subentry2"
      freeTrialEnabled={true}
    />
  );

  expect(screen.queryAllByTestId('pim-main-menu-item')).toHaveLength(3);
  expect(screen.queryAllByTestId('locked-entry')).toHaveLength(1);
});

test('It displays different url according to pim version', async () => {
  // @ts-ignore
  global.fetch = () =>
    Promise.resolve({
      ok: true,
      status: 200,
      json: () => Promise.resolve({pim_version: '6.7', pim_edition: 'ce'}),
    });

  renderWithProviders(
    <PimNavigation
      entries={aMainNavigation()}
      activeEntryCode="entry10"
      activeSubEntryCode="subentry2"
      freeTrialEnabled={true}
    />
  );

  expect(screen.getByText('pim_menu.tab.help.title')).toBeInTheDocument();
  fireEvent.mouseOver(screen.getByText('pim_menu.tab.help.title'));

  const helpCenterUrl = screen.getByText('pim_menu.tab.help.help_center');

  await waitFor(() => !!helpCenterUrl);
  expect(screen.getByText('pim_menu.tab.help.help_center')).toBeVisible();
  expect(helpCenterUrl).toHaveAttribute(
    'href',
    'https://help.akeneo.com/pim/v6/index.html?utm_source=akeneo-app&utm_medium=interrogation-icon&utm_campaign=ce6.7'
  );

  fireEvent.mouseLeave(screen.getByText('pim_menu.tab.help.title'));
  expect(screen.getByText('pim_menu.tab.help.help_center')).not.toBeVisible();
});
