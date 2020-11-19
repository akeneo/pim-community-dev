import React from 'react';

import '@testing-library/jest-dom/extend-expect';
import {fireEvent, render, waitFor} from '@testing-library/react';

import fetchCategoryTrees from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchCategoryTrees';
import fetchCategoryChildren from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchCategoryChildren';
import CategoryFilter from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/Overview/Filters/CategoryFilter';
import {DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY} from '@akeneo-pim-community/data-quality-insights/src';
import {renderDashboardWithProvider} from '../../utils/render/renderDashboardWithProvider';

const UserContext = require('pim/user-context');

jest.mock('pim/user-context');
jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchCategoryTrees');
jest.mock('@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/Dashboard/fetchCategoryChildren');

beforeEach(() => {
  jest.resetModules();
});

window.dispatchEvent = jest.fn();
UserContext.get.mockReturnValue('en_US');

describe('Dashboard > filter on category', () => {
  test('dashboard can be filtered on "Digital cameras" category', async () => {
    fetchCategoryTrees.mockResolvedValue(categoryTrees);
    fetchCategoryChildren.mockResolvedValueOnce(masterChildren).mockResolvedValueOnce(cameraChildren);

    const {getByTestId, getByText, baseElement} = renderDashboardWithProvider(<CategoryFilter categoryCode={null} />);

    await openCategoryFilterModal(getByTestId);
    await navigateToDigitalCamerasCategory(getByText, getByTestId);
    await selectDigitalCamerasCategory(getByText, getByTestId);

    fireEvent.click(getByTestId('dqiValidateModal'));

    assertDigitalCameraCategoryFilterIsAppliedOnDashboard(baseElement);
    assertCategoryFilterEventHasBeenDispatched();
  });
});

async function openCategoryFilterModal(getByTestId) {
  fireEvent.click(getByTestId('dqiCategoryFilter'));
  await waitFor(() => getByTestId('dqiModal'));
}

async function navigateToDigitalCamerasCategory(getByText, getByTestId) {
  const cameraCategory = await waitFor(() => getByText('Cameras'));
  expect(cameraCategory).toBeTruthy();

  const cameraChildOpeningIcon = await waitFor(() => getByTestId('dqiChildOpeningIcon_4'));
  expect(cameraChildOpeningIcon).toBeTruthy();
  fireEvent.click(cameraChildOpeningIcon);
}

async function selectDigitalCamerasCategory(getByText, getByTestId) {
  const digitalCameraLabel = await waitFor(() => getByText('Digital cameras'));
  fireEvent.click(digitalCameraLabel);

  const digitalCameraNode = await waitFor(() => getByTestId('dqiChildNode_5'));
  expect(digitalCameraNode.className.includes('jstree-checked')).toBeTruthy();
}

function assertDigitalCameraCategoryFilterIsAppliedOnDashboard(baseElement: HTMLElement) {
  expect(baseElement.textContent.includes('Digital cameras')).toBeTruthy();
}

function assertCategoryFilterEventHasBeenDispatched() {
  const customEvents = window.dispatchEvent.mock.calls.filter(event => event[0].constructor.name === 'CustomEvent')[0];
  expect(customEvents.length).toBe(1);
  expect(customEvents[0].type).toBe(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY);
  expect(customEvents[0].detail.categoryCode).toBe('digital_cameras');
}

const categoryTrees = [
  {
    code: 'master',
    labels: {
      fr_FR: 'Catalogue principal',
      en_US: 'Master catalog',
      de_DE: 'Hauptkatalog',
    },
    id: 1,
  },
  {
    code: 'sales',
    labels: {
      fr_FR: 'Catalogue des ventes',
      en_US: 'Sales catalog',
      de_DE: 'Katalog Umsatz',
    },
    id: 2,
  },
];

const masterChildren = {
  children: [
    {
      attr: {
        id: 'node_3',
        'data-code': 'tvs_projectors',
      },
      data: 'TVs and projectors',
      state: 'closed',
    },
    {
      attr: {
        id: 'node_4',
        'data-code': 'cameras',
      },
      data: 'Cameras',
      state: 'closed',
    },
  ],
};

const cameraChildren = {
  children: [
    {
      attr: {
        id: 'node_5',
        'data-code': 'digital_cameras',
      },
      data: 'Digital cameras',
      state: 'leaf',
    },
    {
      attr: {
        id: 'node_6',
        'data-code': 'camcorders',
      },
      data: 'Camcorders',
      state: 'leaf',
    },
  ],
};
