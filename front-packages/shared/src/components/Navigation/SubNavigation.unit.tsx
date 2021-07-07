import React from 'react';
import {mockedDependencies, renderWithProviders} from '../../tests';
import {SubNavigation} from './SubNavigation';
import {fireEvent, screen} from '@testing-library/react';
import {aSubNavigationMenu} from './navigationHelper';

beforeEach(() => {
  sessionStorage.clear();
});

const {sections, subNavigationEntries} = aSubNavigationMenu();

test('It displays the sub navigation', () => {
  renderWithProviders(
    <SubNavigation entries={subNavigationEntries} activeSubEntryCode={'subentry2'} sections={sections}/>
  );

  expect(screen.getByText('Section 1')).toBeInTheDocument();
  expect(screen.getByText('Section 2')).toBeInTheDocument();
  expect(screen.queryAllByRole('menuitem')).toHaveLength(3);
});

test('It can display a collapsed menu by default', () => {
  sessionStorage.setItem('collapsedColumn_menu', '0');
  renderWithProviders(
    <SubNavigation entries={subNavigationEntries} activeSubEntryCode={'subentry2'} sections={sections} stateCode={'menu'}/>
  );

  expect(screen.queryAllByRole('menuitem')).toHaveLength(0);
});

test('The menu can be collapsed manually', () => {
  sessionStorage.setItem('collapsedColumn_menu', '1');
  renderWithProviders(
    <SubNavigation entries={subNavigationEntries} activeSubEntryCode={'subentry2'} sections={sections} stateCode={'menu'}/>
  );
  expect(screen.queryAllByRole('menuitem')).toHaveLength(3);

  fireEvent.click(screen.getByTestId('open-subnavigation-button'));

  expect(screen.queryAllByRole('menuitem')).toHaveLength(0);
  expect(sessionStorage.getItem('collapsedColumn_menu')).toBe('0');
});

test('It redirects the user to the clicked entry route', () => {
  renderWithProviders(
    <SubNavigation entries={subNavigationEntries} activeSubEntryCode={'subentry2'} sections={sections}/>
  );

  mockedDependencies.router.redirect = jest.fn();
  fireEvent.click(screen.getByText('Sub entry 1'));
  expect(mockedDependencies.router.redirect).toHaveBeenCalledWith('subentry1_route');
});
