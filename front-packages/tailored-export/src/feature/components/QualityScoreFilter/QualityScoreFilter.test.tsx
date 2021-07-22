import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders} from '@akeneo-pim-community/shared';
import {QualityScoreFilter} from './QualityScoreFilter';

const channels: Channel[] = [
  {
    code: 'ecommerce',
    labels: {},
    locales: [
      {
        code: 'en_US',
        label: 'English',
        region: 'US',
        language: 'en',
      },
      {
        code: 'fr_FR',
        label: 'Français',
        region: 'FR',
        language: 'fr',
      },
      {
        code: 'br_FR',
        label: 'Breton',
        region: 'bzh',
        language: 'br',
      },
    ],
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
  },
  {
    code: 'print',
    labels: {},
    locales: [
      {
        code: 'en_US',
        label: 'en_US',
        region: 'US',
        language: 'en',
      },
      {
        code: 'fr_FR',
        label: 'fr_FR',
        region: 'FR',
        language: 'fr',
      },
    ],
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
  },
];

jest.mock('../../hooks/useChannels', () => ({
  useChannels: () => channels,
}));

const availableOperators = ['IN AT LEAST ONE LOCALE', 'IN ALL LOCALES'];
test('it does not display the channel, locale and operator selectors depending if a quality score is not selected', () => {
  renderWithProviders(
    <QualityScoreFilter
      availableOperators={availableOperators}
      filter={{
        field: 'quality_score_multi_locales',
        operator: null,
        value: [],
      }}
      onChange={() => {}}
      validationErrors={[]}
    />
  );

  expect(screen.getByText(`pim_enrich.export.product.filter.quality-score.title`)).toBeInTheDocument();
  expect(
    screen.queryByText('pim_enrich.export.product.filter.quality-score.operator_choice_title')
  ).not.toBeInTheDocument();
  expect(screen.queryByText('pim_common.channel')).not.toBeInTheDocument();
  expect(screen.queryByText('akeneo.tailored_export.filters.quality_score.locales.label')).not.toBeInTheDocument();
});

test('it displays the channel, locale and operator selectors depending if a quality score selected', () => {
  renderWithProviders(
    <QualityScoreFilter
      availableOperators={availableOperators}
      filter={{
        field: 'quality_score_multi_locales',
        operator: 'IN AT LEAST ONE LOCALE',
        value: [1],
      }}
      onChange={() => {}}
      validationErrors={[]}
    />
  );

  expect(screen.getByText(`pim_enrich.export.product.filter.quality-score.title`)).toBeInTheDocument();
  expect(screen.getByText('pim_enrich.export.product.filter.quality-score.operator_choice_title')).toBeInTheDocument();
  expect(screen.getByText('pim_common.channel')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_export.filters.quality_score.locales.label')).toBeInTheDocument();
});

test('when a quality score is selected, the operator, channel and locale selectors are initialized with default values', () => {
  const handleFilterChange = jest.fn();
  renderWithProviders(
    <QualityScoreFilter
      availableOperators={availableOperators}
      filter={{
        field: 'quality_score_multi_locales',
        operator: null,
        value: [],
      }}
      onChange={handleFilterChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByText('pim_enrich.export.product.filter.quality-score.title'));
  userEvent.click(screen.getByText('A'));

  expect(handleFilterChange).toHaveBeenCalledWith({
    field: 'quality_score_multi_locales',
    operator: 'IN AT LEAST ONE LOCALE',
    value: [1],
    context: {
      scope: 'ecommerce', // first channel in the list
      locales: ['en_US', 'fr_FR', 'br_FR'], // All locales of the channel 'ecommerce'
    },
  });
});

test('it can switch operator', () => {
  const handleFilterChange = jest.fn();
  renderWithProviders(
    <QualityScoreFilter
      availableOperators={availableOperators}
      filter={{
        field: 'quality_score_multi_locales',
        operator: 'IN AT LEAST ONE LOCALE',
        value: [1],
      }}
      onChange={handleFilterChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByText('pim_enrich.export.product.filter.quality-score.operator_choice_title'));
  userEvent.click(screen.getByText('pim_enrich.export.product.filter.quality-score.operators.IN ALL LOCALES'));

  expect(handleFilterChange).toHaveBeenCalledWith({
    field: 'quality_score_multi_locales',
    operator: 'IN ALL LOCALES',
    value: [1],
  });
});

test('it can switch channel and initializes the locales value with all locales activated for this channel', () => {
  const handleFilterChange = jest.fn();
  renderWithProviders(
    <QualityScoreFilter
      availableOperators={availableOperators}
      filter={{
        field: 'quality_score_multi_locales',
        operator: 'IN AT LEAST ONE LOCALE',
        value: [1],
      }}
      onChange={handleFilterChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.channel'));
  userEvent.click(screen.getByText('[print]'));

  expect(handleFilterChange).toHaveBeenCalledWith({
    field: 'quality_score_multi_locales',
    operator: 'IN AT LEAST ONE LOCALE',
    value: [1],
    context: {
      scope: 'print',
      locales: ['en_US', 'fr_FR'],
    },
  });
});

test('it filters the locales that do not belong to a channel when the channel changes', () => {
  const handleFilterChange = jest.fn();
  renderWithProviders(
    <QualityScoreFilter
      availableOperators={availableOperators}
      filter={{
        field: 'quality_score_multi_locales',
        operator: 'IN AT LEAST ONE LOCALE',
        value: [1],
        context: {
          scope: 'ecommerce',
          locales: ['fr_FR', 'br_FR'],
        },
      }}
      onChange={handleFilterChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.channel'));
  userEvent.click(screen.getByText('[print]'));

  expect(handleFilterChange).toHaveBeenCalledWith({
    field: 'quality_score_multi_locales',
    operator: 'IN AT LEAST ONE LOCALE',
    value: [1],
    context: {
      scope: 'print',
      locales: ['en_US', 'fr_FR'],
    },
  });
});

test('it can switch locales', () => {
  const handleFilterChange = jest.fn();
  renderWithProviders(
    <QualityScoreFilter
      availableOperators={availableOperators}
      filter={{
        field: 'quality_score_multi_locales',
        operator: 'IN AT LEAST ONE LOCALE',
        value: [1],
        context: {
          scope: 'ecommerce',
          locales: ['fr_FR'],
        },
      }}
      onChange={handleFilterChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_export.filters.quality_score.locales.label'));
  userEvent.click(screen.getByText('Breton'));

  expect(handleFilterChange).toHaveBeenCalledWith({
    field: 'quality_score_multi_locales',
    operator: 'IN AT LEAST ONE LOCALE',
    value: [1],
    context: {
      scope: 'ecommerce',
      locales: ['fr_FR', 'br_FR'],
    },
  });
});

test('when the user switch resets the quality score it removes the context and the operator', () => {
  const handleFilterChange = jest.fn();
  renderWithProviders(
    <QualityScoreFilter
      availableOperators={availableOperators}
      filter={{
        field: 'quality_score_multi_locales',
        operator: 'IN AT LEAST ONE LOCALE',
        value: [1],
      }}
      onChange={handleFilterChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByText('pim_enrich.export.product.filter.quality-score.title'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.filters.quality_score.quality_score.remove'));

  expect(handleFilterChange).toHaveBeenCalledWith({
    field: 'quality_score_multi_locales',
    operator: null,
    value: [],
  });
});

test('it displays validation errors', () => {
  const qualityScoreErrorMessage = 'error with qualityScore';
  const operatorErrorMessage = 'error with operator';
  const channelErrorMessage = 'error with channel';
  const localeErrorMessage = 'error with locale';

  renderWithProviders(
    <QualityScoreFilter
      availableOperators={availableOperators}
      filter={{
        field: 'quality_score_multi_locales',
        operator: 'IN AT LEAST ONE LOCALE',
        value: [1],
      }}
      onChange={() => {}}
      validationErrors={[
        {
          messageTemplate: qualityScoreErrorMessage,
          parameters: {},
          message: '',
          propertyPath: '[value]',
          invalidValue: '',
        },
        {
          messageTemplate: operatorErrorMessage,
          parameters: {},
          message: '',
          propertyPath: '[operator]',
          invalidValue: '',
        },
        {
          messageTemplate: channelErrorMessage,
          parameters: {},
          message: '',
          propertyPath: '[context][scope]',
          invalidValue: '',
        },
        {
          messageTemplate: localeErrorMessage,
          parameters: {},
          message: '',
          propertyPath: '[context][locales]',
          invalidValue: '',
        },
      ]}
    />
  );

  expect(screen.getByText(qualityScoreErrorMessage)).toBeInTheDocument();
  expect(screen.getByText(operatorErrorMessage)).toBeInTheDocument();
  expect(screen.getByText(channelErrorMessage)).toBeInTheDocument();
  expect(screen.getByText(localeErrorMessage)).toBeInTheDocument();
});
