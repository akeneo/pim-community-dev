import React from 'react';
import {mockedDependencies, renderWithProviders} from '../../tests';
import {SubNavigation, SubNavigationEntry, SubNavigationSection} from './SubNavigation';
import {fireEvent, screen} from '@testing-library/react';

beforeEach(() => {
  sessionStorage.clear();
});

test('It displays the sub navigation', () => {
  renderWithProviders(
    <SubNavigation entries={entries} activeSubEntryCode={'entry2'} sections={sections}/>
  );

  expect(screen.getByText('Section 1')).toBeInTheDocument();
  expect(screen.getByText('Section 2')).toBeInTheDocument();
  expect(screen.queryAllByRole('menuitem')).toHaveLength(3);
});

test('It can display a collapsed menu by default', () => {
  sessionStorage.setItem('collapsedColumn_menu', '0');
  renderWithProviders(
    <SubNavigation entries={entries} activeSubEntryCode={'entry2'} sections={sections} stateCode={'menu'}/>
  );

  expect(screen.queryAllByRole('menuitem')).toHaveLength(0);
});

test('The menu can be collapsed manually', () => {
  sessionStorage.setItem('collapsedColumn_menu', '1');
  renderWithProviders(
    <SubNavigation entries={entries} activeSubEntryCode={'entry2'} sections={sections} stateCode={'menu'}/>
  );
  expect(screen.queryAllByRole('menuitem')).toHaveLength(3);

  fireEvent.click(screen.getByTestId('open-subnavigation-button'));

  expect(screen.queryAllByRole('menuitem')).toHaveLength(0);
  expect(sessionStorage.getItem('collapsedColumn_menu')).toBe('0');
});

test('It redirects the user to the clicked entry route', () => {
  renderWithProviders(
    <SubNavigation entries={entries} activeSubEntryCode={'entry2'} sections={sections}/>
  );

  mockedDependencies.router.redirect = jest.fn();
  fireEvent.click(screen.getByText('Entry 1'));
  expect(mockedDependencies.router.redirect).toHaveBeenCalledWith('entry1_route');
});

const sections: SubNavigationSection[] = [
  {
    code: 'section1',
    title: 'Section 1',
  },
  {
    code: 'section2',
    title: 'Section 2',
  }
];

const entries: SubNavigationEntry[] = [
  {
    code: 'entry1',
    sectionCode: 'section1',
    title: 'Entry 1',
    route: 'entry1_route',
  },
  {
    code: 'entry2',
    sectionCode: 'section1',
    title: 'Entry 2',
    route: 'entry2_route',
  },
  {
    code: 'entry3',
    sectionCode: 'section2',
    title: 'Entry 3',
    route: 'entry3_route',
  },
];
