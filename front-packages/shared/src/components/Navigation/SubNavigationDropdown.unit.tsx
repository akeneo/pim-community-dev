import React from 'react';
import {renderWithProviders, mockedDependencies} from '../../tests';
import {SubNavigationDropdown} from './SubNavigationDropdown';
import {SubNavigationEntry} from './SubNavigation';
import {fireEvent, screen} from '@testing-library/react';

test('It displays by default only the open dropdown button', () => {
  renderWithProviders(
    <SubNavigationDropdown title='Sub navigation title' entries={entries}/>
  );

  expect(screen.getByTestId('openSubNavigationDropdownButton')).toBeInTheDocument();
  expect(screen.queryByText('Sub navigation title')).toBeFalsy();
});

test('It opens the dropdown when clicking on the button', () => {
  renderWithProviders(
    <SubNavigationDropdown title='Sub navigation title' entries={entries}/>
  );

  fireEvent.click(screen.getByTestId('openSubNavigationDropdownButton'));
  expect(screen.getByText('Sub navigation title')).toBeInTheDocument();
  expect(screen.getByText('Entry 1')).toBeInTheDocument();
  expect(screen.getByText('Entry 2')).toBeInTheDocument();
});

test('It redirects the user to the entry route and closes the menu when clicking on it', () => {
  renderWithProviders(
    <SubNavigationDropdown title='Sub navigation title' entries={entries}/>
  );

  fireEvent.click(screen.getByTestId('openSubNavigationDropdownButton'));

  mockedDependencies.router.redirect = jest.fn();
  fireEvent.click(screen.getByText('Entry 1'));

  expect(mockedDependencies.router.redirect).toHaveBeenCalledWith('route_test');
  expect(screen.queryByText('Sub navigation title')).toBeFalsy();
});

const entries: SubNavigationEntry[] = [
  {
    code: 'entry1',
    sectionCode: '',
    title: 'Entry 1',
    route: 'route_test',
  },
  {
    code: 'entry2',
    sectionCode: '',
    title: 'Entry 2',
    route: '',
  },
];
