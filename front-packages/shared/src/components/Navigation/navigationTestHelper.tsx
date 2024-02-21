import React from 'react';
import {SubNavigationEntry, SubNavigationSection} from './SubNavigation';
import {NavigationEntry} from './PimNavigation';
import {CardIcon} from 'akeneo-design-system';

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
    disabled: true,
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
  },
];

const aSubNavigationMenu = () => {
  return {
    subNavigationEntries,
    sections,
  };
};

const aMainNavigation = (): NavigationEntry[] => {
  return [
    {
      code: 'entry1',
      title: 'Entry 1',
      route: 'entry1_route',
      icon: <CardIcon />,
      subNavigations: [
        {
          entries: subNavigationEntries,
          sections: sections,
        },
      ],
      align: 'bottom',
      isLandingSectionPage: false,
    },
    {
      code: 'entry2',
      title: 'Entry 2',
      route: 'entry2_route',
      icon: <CardIcon />,
      subNavigations: [],
      isLandingSectionPage: true,
    },
    {
      code: 'entry3',
      title: 'Entry 3',
      route: 'entry3_route',
      icon: <CardIcon />,
      subNavigations: [],
      isLandingSectionPage: false,
      disabled: true,
    },
  ];
};

export {aMainNavigation, aSubNavigationMenu};
