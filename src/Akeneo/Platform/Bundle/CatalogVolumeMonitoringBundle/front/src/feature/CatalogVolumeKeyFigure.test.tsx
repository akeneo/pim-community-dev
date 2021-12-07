import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {CatalogVolumeKeyFigure} from './CatalogVolumeKeyFigure';
import {AverageMaxValue, CatalogVolume} from './model/catalog-volume';

test('it renders key figure of type count', () => {
  const countAttributes: CatalogVolume = {
    name: 'count_attributes',
    type: 'count',
    value: 112,
  };

  renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={countAttributes} />);

  expect(screen.getByText(112)).toBeInTheDocument();
});

test('it does not render key figure of type count when value is an object', () => {
  const countAttributes: CatalogVolume = {
    name: 'count_attributes',
    type: 'count',
    value: {
      average: 4,
      max: 43,
    },
  };

  renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={countAttributes} />);

  expect(screen.queryByText('count_attributes.axis.count_attributes')).not.toBeInTheDocument();
});

test('it renders key figure of type average', () => {
  const catalogVolumeAverageMaxAttributesPerFamily: CatalogVolume = {
    name: 'average_max_attributes_per_family',
    type: 'average_max',
    value: {
      average: 4,
      max: 43,
    },
  };

  renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={catalogVolumeAverageMaxAttributesPerFamily} />);

  expect(typeof catalogVolumeAverageMaxAttributesPerFamily.value).toBe('object');
  expect(typeof (catalogVolumeAverageMaxAttributesPerFamily.value as AverageMaxValue).average).not.toBeUndefined();
  expect(screen.getByText('pim_catalog_volume.axis.average_max_attributes_per_family')).toBeInTheDocument();
  expect(screen.getByText(43)).toBeInTheDocument();
});

test('it does not render key figure of type average when the value is not an object', () => {
  const catalogVolumeAverageMaxAttributesPerFamilyWrongFormat: CatalogVolume = {
    name: 'average_max_attributes_per_family',
    type: 'average_max',
    value: 4,
  };

  renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={catalogVolumeAverageMaxAttributesPerFamilyWrongFormat} />);

  expect(screen.queryByText('pim_catalog_volume.axis.average_max_attributes_per_family')).not.toBeInTheDocument();
});
