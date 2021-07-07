import React from 'react';
import {mockedDependencies, renderWithProviders} from '../../tests';
import {SubNavigationSection, SubNavigationType, SubNavigationEntry} from './SubNavigation';
import {fireEvent, screen} from '@testing-library/react';
import {PimNavigation, NavigationEntry} from './PimNavigation';
import {CardIcon} from 'akeneo-design-system';

jest.mock('../PimView');

test('It displays the main navigation with an active entry and a sub menu', async() => {
  renderWithProviders(
    <PimNavigation entries={mainNavigationEntries} activeEntryCode='entry1' activeSubEntryCode='subentry2'/>
  );

  expect(screen.getByLabelText('Main navigation')).toBeInTheDocument();
  expect(screen.getByTestId('pim-sub-menu')).toBeInTheDocument();
  expect(screen.queryAllByTestId('pim-main-menu-item')).toHaveLength(2);
});

test('It displays the main navigation with an active entry without a sub menu', async() => {
  renderWithProviders(
    <PimNavigation entries={mainNavigationEntries} activeEntryCode='entry2' activeSubEntryCode={null}/>
  );

  expect(screen.queryAllByTestId('pim-main-menu-item')).toHaveLength(2);
  expect(screen.getByLabelText('Main navigation')).toBeInTheDocument();
  expect(screen.queryByTestId('pim-sub-menu')).toBeFalsy();
});

test('It redirects the user to the clicked entry route', async() => {
  renderWithProviders(
    <PimNavigation entries={mainNavigationEntries} activeEntryCode='entry1' activeSubEntryCode='subentry2'/>
  );

  mockedDependencies.router.redirect = jest.fn();
  fireEvent.click(screen.getByText('Entry 1'));
  expect(mockedDependencies.router.redirect).toHaveBeenCalledWith('entry1_route');
});

const subNavigationEntries: SubNavigationEntry[] = [
  {
    code: 'subentry1',
    sectionCode: 'section1',
    title: 'Sub entry 1',
    route: 'subentry1_route',
  },
  {
    code: 'subentry2',
    sectionCode: 'section1',
    title: 'Sub entry 2',
    route: 'subentry2_route',
  },
  {
    code: 'subentry3',
    sectionCode: 'section2',
    title: 'Sub entry 3',
    route: 'subentry3_route',
  },
];

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

const subNavigations: SubNavigationType[] = [
  {
    entries: subNavigationEntries,
    sections: sections,
  }
];

const mainNavigationEntries: NavigationEntry[] = [
  {
    code: 'entry1',
    title: 'Entry 1',
    route: 'entry1_route',
    icon: <CardIcon/>,
    subNavigations: subNavigations,
    isLandingSectionPage: false,
  },
  {
    code: 'entry2',
    title: 'Entry 2',
    route: 'entry2_route',
    icon: <CardIcon/>,
    subNavigations: [],
    isLandingSectionPage: true,
  }
];
