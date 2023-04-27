import React from 'react';
import {renderWithProviders, mockedDependencies} from '../../tests';
import {SubNavigationDropdown} from './SubNavigationDropdown';
import {fireEvent, screen} from '@testing-library/react';
import {aSubNavigationMenu} from './navigationTestHelper';

const {subNavigationEntries} = aSubNavigationMenu();

test('It displays by default only the open dropdown button', () => {
  renderWithProviders(<SubNavigationDropdown title="Sub navigation title" entries={subNavigationEntries} />);

  expect(screen.getByTestId('openSubNavigationDropdownButton')).toBeInTheDocument();
  expect(screen.queryByText('Sub navigation title')).toBeFalsy();
});

test('It opens the dropdown when clicking on the button', () => {
  renderWithProviders(<SubNavigationDropdown title="Sub navigation title" entries={subNavigationEntries} />);

  fireEvent.click(screen.getByTestId('openSubNavigationDropdownButton'));
  expect(screen.getByText('Sub navigation title')).toBeInTheDocument();
  expect(screen.getByText('Sub entry 1')).toBeInTheDocument();
  expect(screen.getByText('Sub entry 2')).toBeInTheDocument();
  expect(screen.getByText('Sub entry 3')).toBeInTheDocument();
});

test('It redirects the user to the entry route and closes the menu when clicking on it', () => {
  renderWithProviders(<SubNavigationDropdown title="Sub navigation title" entries={subNavigationEntries} />);

  fireEvent.click(screen.getByTestId('openSubNavigationDropdownButton'));

  fireEvent.click(screen.getByText('Sub entry 1'));

  expect(mockedDependencies.router?.redirect).toHaveBeenCalledWith('subentry1_route');
  expect(screen.queryByText('Sub navigation title')).toBeFalsy();
});
