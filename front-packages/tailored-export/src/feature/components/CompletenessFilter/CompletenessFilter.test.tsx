import React from 'react';
import {act, screen} from '@testing-library/react';
import {Channel, renderWithProviders} from '@akeneo-pim-community/shared';
import {CompletenessFilter, Operator} from './CompletenessFilter';
import {FetcherContext} from '../../contexts';
import {Attribute} from '../../models';
import userEvent from '@testing-library/user-event';

const fetchers = {
  attribute: {
    fetchByIdentifiers: (): Promise<Attribute[]> =>
      new Promise(resolve => {
        act(() => {
          resolve([]);
        });
      }),
  },
  channel: {
    fetchAll: (): Promise<Channel[]> =>
      new Promise(resolve => {
        act(() => {
          resolve([
            {
              code: 'Ecommerce',
              labels: {},
              locales: [
                {
                  code: 'en_US',
                  label: 'English',
                  region: '',
                  language: '',
                },
                {
                  code: 'fr_FR',
                  label: 'French',
                  region: '',
                  language: '',
                },
                {
                  code: 'br_FR',
                  label: 'Breton',
                  region: '',
                  language: '',
                },
              ],
            },
          ]);
        });
      }),
  },
};

const operatorsAndVisibility = [
  {operator: 'ALL', shouldAppear: false},
  {operator: 'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE', shouldAppear: true},
  {operator: 'GREATER OR EQUALS THAN ON ALL LOCALES', shouldAppear: true},
  {operator: 'LOWER THAN ON ALL LOCALES', shouldAppear: true},
] as const;
let availableOperators = operatorsAndVisibility.map(operatorAndVisibility => operatorAndVisibility.operator);

test.each(operatorsAndVisibility)(
  'it displays the locale selector depending on the operator',
  async ({operator, shouldAppear}: {operator: Operator; shouldAppear: boolean}) => {
    await act(async () => {
      renderWithProviders(
        <FetcherContext.Provider value={fetchers}>
          <CompletenessFilter
            availableOperators={availableOperators}
            filter={{
              field: 'completeness',
              value: 100,
              operator: operator,
              context: {locales: ['fr_FR', 'en_US']},
            }}
            onChange={() => {}}
            validationErrors={[]}
          />
        </FetcherContext.Provider>
      );
    });

    expect(screen.getByText(`pim_enrich.export.product.filter.completeness.operators.${operator}`)).toBeInTheDocument();

    if (shouldAppear) {
      expect(screen.getByText('akeneo.tailored_export.filters.completeness.locales.label')).toBeInTheDocument();
    } else {
      expect(screen.queryByText('akeneo.tailored_export.filters.completeness.locales.label')).not.toBeInTheDocument();
    }
  }
);

test('it can switch operator', async () => {
  const handleOperatorChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <CompletenessFilter
          availableOperators={availableOperators}
          filter={{
            field: 'completeness',
            value: 100,
            operator: 'ALL',
            context: {locales: ['fr_FR', 'en_US']},
          }}
          onChange={handleOperatorChange}
          validationErrors={[]}
        />
      </FetcherContext.Provider>
    );
  });

  const openDropdownButton = screen.getByTitle(`pim_common.open`);
  userEvent.click(openDropdownButton);
  const greaterThanButton = screen.getByText(
    `pim_enrich.export.product.filter.completeness.operators.GREATER OR EQUALS THAN ON ALL LOCALES`
  );
  await userEvent.click(greaterThanButton);

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: ['fr_FR', 'en_US']},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it can select locales', async () => {
  const handleLocalesChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <CompletenessFilter
          availableOperators={availableOperators}
          filter={{
            field: 'completeness',
            value: 100,
            operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
            context: {locales: []},
          }}
          onChange={handleLocalesChange}
          validationErrors={[]}
        />
      </FetcherContext.Provider>
    );
  });

  const openDropdownButton = screen.getAllByTitle('pim_common.open')[1];
  userEvent.click(openDropdownButton);
  const greaterThanButton = screen.getByText('English');
  await userEvent.click(greaterThanButton);

  expect(handleLocalesChange).toHaveBeenCalledWith({
    context: {locales: ['en_US']},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it displays locales validation errors', async () => {
  const localesErrorMessage = 'error with the locales';

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <CompletenessFilter
          availableOperators={availableOperators}
          filter={{
            field: 'completeness',
            value: 100,
            operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
            context: {locales: []},
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
      </FetcherContext.Provider>
    );
  });

  expect(screen.getByText(localesErrorMessage)).toBeInTheDocument();
});

test('it displays operator validation errors', async () => {
  const operatorErrorMessage = 'error with the operator';

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <CompletenessFilter
          availableOperators={availableOperators}
          filter={{
            field: 'completeness',
            value: 100,
            operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
            context: {locales: []},
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
      </FetcherContext.Provider>
    );
  });

  expect(screen.getByText(operatorErrorMessage)).toBeInTheDocument();
});
