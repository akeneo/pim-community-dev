import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders} from '@akeneo-pim-community/shared';
import {CompletenessFilter, Operator} from './CompletenessFilter';

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

const operatorsAndVisibility = [
  {operator: 'ALL', shouldAppear: false},
  {operator: 'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE', shouldAppear: true},
  {operator: 'GREATER OR EQUALS THAN ON ALL LOCALES', shouldAppear: true},
  {operator: 'LOWER THAN ON ALL LOCALES', shouldAppear: true},
] as const;
let availableOperators = operatorsAndVisibility.map(operatorAndVisibility => operatorAndVisibility.operator);

test.each(operatorsAndVisibility)(
  'it displays the locale selector depending on the operator',
  ({operator, shouldAppear}: {operator: Operator; shouldAppear: boolean}) => {
    renderWithProviders(
      <CompletenessFilter
        availableOperators={availableOperators}
        filter={{
          field: 'completeness',
          value: 100,
          operator: operator,
          context: {locales: ['fr_FR', 'en_US'], scope: 'ecommerce'},
        }}
        onChange={() => {}}
        validationErrors={[]}
      />
    );

    expect(screen.getByText(`pim_enrich.export.product.filter.completeness.operators.${operator}`)).toBeInTheDocument();

    if (shouldAppear) {
      expect(screen.getByText('akeneo.tailored_export.filters.completeness.locales.label')).toBeInTheDocument();
    } else {
      expect(screen.queryByText('akeneo.tailored_export.filters.completeness.locales.label')).not.toBeInTheDocument();
    }
  }
);

test('it can switch operator', () => {
  const handleOperatorChange = jest.fn();

  renderWithProviders(
    <CompletenessFilter
      availableOperators={availableOperators}
      filter={{
        field: 'completeness',
        value: 100,
        operator: 'LOWER THAN ON ALL LOCALES',
        context: {locales: ['fr_FR'], scope: 'ecommerce'},
      }}
      onChange={handleOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getAllByTitle('pim_common.open')[0]);
  userEvent.click(
    screen.getByText('pim_enrich.export.product.filter.completeness.operators.GREATER OR EQUALS THAN ON ALL LOCALES')
  );

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: ['fr_FR'], scope: 'ecommerce'},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it initializes the context when the user switch operator from "ALL" to another operator', () => {
  const handleOperatorChange = jest.fn();

  renderWithProviders(
    <CompletenessFilter
      availableOperators={availableOperators}
      filter={{
        field: 'completeness',
        value: 100,
        operator: 'ALL',
      }}
      onChange={handleOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getAllByTitle('pim_common.open')[0]);
  userEvent.click(
    screen.getByText('pim_enrich.export.product.filter.completeness.operators.GREATER OR EQUALS THAN ON ALL LOCALES')
  );

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: [], scope: 'ecommerce'},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it removes the context when the user switch the operator to "ALL"', () => {
  const handleOperatorChange = jest.fn();

  renderWithProviders(
    <CompletenessFilter
      availableOperators={availableOperators}
      filter={{
        field: 'completeness',
        value: 100,
        operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
        context: {locales: ['fr_FR', 'en_US'], scope: 'ecommerce'},
      }}
      onChange={handleOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getAllByTitle('pim_common.open')[0]);
  userEvent.click(screen.getByText('pim_enrich.export.product.filter.completeness.operators.ALL'));

  expect(handleOperatorChange).toHaveBeenCalledWith({
    field: 'completeness',
    operator: 'ALL',
    value: 100,
  });
});

test('it keeps the channel when switching an operator that is not "ALL"', () => {
  const handleOperatorChange = jest.fn();

  renderWithProviders(
    <CompletenessFilter
      availableOperators={availableOperators}
      filter={{
        field: 'completeness',
        value: 100,
        operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
        context: {locales: ['fr_FR', 'en_US'], scope: 'ecommerce'},
      }}
      onChange={handleOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getAllByTitle('pim_common.open')[0]);
  userEvent.click(
    screen.getByText('pim_enrich.export.product.filter.completeness.operators.LOWER THAN ON ALL LOCALES')
  );

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: ['fr_FR', 'en_US'], scope: 'ecommerce'},
    field: 'completeness',
    operator: 'LOWER THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it selects a channel', () => {
  const handleOperatorChange = jest.fn();

  renderWithProviders(
    <CompletenessFilter
      availableOperators={availableOperators}
      filter={{
        field: 'completeness',
        value: 100,
        operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
        context: {locales: ['fr_FR', 'en_US'], scope: 'ecommerce'},
      }}
      onChange={handleOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.channel'));
  userEvent.click(screen.getByText('[print]'));

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: ['fr_FR', 'en_US'], scope: 'print'},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it filters the locales that do not belong to a channel when the channel changes', () => {
  const handleOperatorChange = jest.fn();

  renderWithProviders(
    <CompletenessFilter
      availableOperators={availableOperators}
      filter={{
        field: 'completeness',
        value: 100,
        operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
        context: {locales: ['fr_FR', 'en_US', 'br_FR'], scope: 'ecommerce'},
      }}
      onChange={handleOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.channel'));
  userEvent.click(screen.getByText('[print]'));

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: ['fr_FR', 'en_US'], scope: 'print'},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it can select locales', () => {
  const handleLocalesChange = jest.fn();

  renderWithProviders(
    <CompletenessFilter
      availableOperators={availableOperators}
      filter={{
        field: 'completeness',
        value: 100,
        operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
        context: {locales: ['en_US', 'fr_FR'], scope: 'ecommerce'},
      }}
      onChange={handleLocalesChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_export.filters.completeness.locales.label'));
  userEvent.click(screen.getByText('Breton'));

  expect(handleLocalesChange).toHaveBeenCalledWith({
    context: {locales: ['en_US', 'fr_FR', 'br_FR'], scope: 'ecommerce'},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it displays locales validation errors', () => {
  const localesErrorMessage = 'error with the locales';

  renderWithProviders(
    <CompletenessFilter
      availableOperators={availableOperators}
      filter={{
        field: 'completeness',
        value: 100,
        operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
        context: {locales: [], scope: ''},
      }}
      onChange={() => {}}
      validationErrors={[
        {
          messageTemplate: localesErrorMessage,
          parameters: {},
          message: '',
          propertyPath: '[context][locales]',
          invalidValue: '',
        },
      ]}
    />
  );

  expect(screen.getByText(localesErrorMessage)).toBeInTheDocument();
});

test('it displays operator validation errors', () => {
  const operatorErrorMessage = 'error with the operator';

  renderWithProviders(
    <CompletenessFilter
      availableOperators={availableOperators}
      filter={{
        field: 'completeness',
        value: 100,
        operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
        context: {locales: [], scope: ''},
      }}
      onChange={() => {}}
      validationErrors={[
        {
          messageTemplate: operatorErrorMessage,
          parameters: {},
          message: '',
          propertyPath: '[operator]',
          invalidValue: '',
        },
      ]}
    />
  );

  expect(screen.getByText(operatorErrorMessage)).toBeInTheDocument();
});
