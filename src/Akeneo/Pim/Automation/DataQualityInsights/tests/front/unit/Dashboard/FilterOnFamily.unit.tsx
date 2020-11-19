import React from 'react';

import '@testing-library/jest-dom/extend-expect';
import {fireEvent, render, waitFor} from '@testing-library/react';

import FamilyFilter from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/Overview/Filters/FamilyFilter';
import fetchFamilies from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchFamilies';
import {DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY} from '@akeneo-pim-community/data-quality-insights/src';
import {renderDashboardWithProvider} from '../../utils/render/renderDashboardWithProvider';

const UserContext = require('pim/user-context');

jest.mock('pim/user-context');
jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchFamilies');

beforeEach(() => {
  jest.resetModules();
});

window.dispatchEvent = jest.fn();
UserContext.get.mockReturnValue('en_US');

describe('Dashboard > filter on family', () => {
  test('dashboard can be filtered on "Mugs" family', async () => {
    fetchFamilies.mockResolvedValue(families);

    const {getByTestId} = renderDashboardWithProvider(<FamilyFilter familyCode={null} />);

    await openFamilyFilterDropdown(getByTestId);
    await selectMugsFamily(getByTestId);
    assertFamilyFilterEventHasBeenDispatched();
  });
});

async function openFamilyFilterDropdown(getByTestId) {
  fireEvent.click(getByTestId('dqiFamilyFilter'));
}

async function selectMugsFamily(getByTestId) {
  const mugsLabel = await waitFor(() => getByTestId('dqiFamily_mugs'));
  fireEvent.click(mugsLabel);
}

function assertFamilyFilterEventHasBeenDispatched() {
  const customEvents = window.dispatchEvent.mock.calls.filter(event => event[0].constructor.name === 'CustomEvent')[0];
  expect(customEvents.length).toBe(1);
  expect(customEvents[0].type).toBe(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY);
  expect(customEvents[0].detail.familyCode).toBe('mugs');
}

const families = {
  camcorders: {
    code: 'camcorders',
    labels: {
      fr_FR: 'Caméscopes numériques',
      en_US: 'Camcorders',
      de_DE: 'Digitale Videokameras',
    },
  },
  mugs: {
    code: 'mugs',
    labels: {
      fr_FR: 'Chopes/Mugs',
      en_US: 'Mugs',
      de_DE: 'Tassen',
    },
  },
  pc_monitors: {
    code: 'pc_monitors',
    labels: {
      fr_FR: 'Moniteurs',
      en_US: 'PC Monitors',
      de_DE: 'PC Monitoren',
    },
  },
};
