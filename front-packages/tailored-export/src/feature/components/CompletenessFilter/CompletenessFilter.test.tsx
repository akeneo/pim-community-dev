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
              code: 'ecommerce',
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
            {
              code: 'print',
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
              context: {locales: ['fr_FR', 'en_US'], scope: 'ecommerce'},
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
            operator: 'LOWER THAN ON ALL LOCALES',
            context: {locales: ['fr_FR'], scope: 'ecommerce'},
          }}
          onChange={handleOperatorChange}
          validationErrors={[]}
        />
      </FetcherContext.Provider>
    );
  });

  userEvent.click(screen.getAllByTitle(`pim_common.open`)[0]);
  await userEvent.click(
    screen.getByText(`pim_enrich.export.product.filter.completeness.operators.GREATER OR EQUALS THAN ON ALL LOCALES`)
  );

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: ['fr_FR'], scope: 'ecommerce'},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it initializes the context when the user switch operator from "ALL" to another operator', async () => {
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
          }}
          onChange={handleOperatorChange}
          validationErrors={[]}
        />
      </FetcherContext.Provider>
    );
  });

  userEvent.click(screen.getAllByTitle(`pim_common.open`)[0]);
  await userEvent.click(
    screen.getByText(`pim_enrich.export.product.filter.completeness.operators.GREATER OR EQUALS THAN ON ALL LOCALES`)
  );

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: [], scope: 'ecommerce'},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it removes the context when the user switch the operator to "ALL"', async () => {
  const handleOperatorChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
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
      </FetcherContext.Provider>
    );
  });

  userEvent.click(screen.getAllByTitle(`pim_common.open`)[0]);
  await userEvent.click(screen.getByText(`pim_enrich.export.product.filter.completeness.operators.ALL`));

  expect(handleOperatorChange).toHaveBeenCalledWith({
    field: 'completeness',
    operator: 'ALL',
    value: 100,
  });
});

test('it keeps the channel when switching an operator that is not "ALL"', async () => {
  const handleOperatorChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
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
      </FetcherContext.Provider>
    );
  });

  userEvent.click(screen.getAllByTitle(`pim_common.open`)[0]);
  await userEvent.click(
    screen.getByText(`pim_enrich.export.product.filter.completeness.operators.LOWER THAN ON ALL LOCALES`)
  );

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: ['fr_FR', 'en_US'], scope: 'ecommerce'},
    field: 'completeness',
    operator: 'LOWER THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it selects a channel', async () => {
  const handleOperatorChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
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
      </FetcherContext.Provider>
    );
  });

  userEvent.click(screen.getByLabelText(`pim_common.channel`));
  await userEvent.click(screen.getByText(`[print]`));

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: ['fr_FR', 'en_US'], scope: 'print'},
    field: 'completeness',
    operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
    value: 100,
  });
});

test('it filters the locales that do not belong to a channel when the channel changes', async () => {
  const handleOperatorChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <CompletenessFilter
          availableOperators={availableOperators}
          filter={{
            field: 'completeness',
            value: 100,
            operator: 'GREATER OR EQUALS THAN ON ALL LOCALES',
            context: {locales: ['fr_FR', 'en_US', 'breton'], scope: 'ecommerce'},
          }}
          onChange={handleOperatorChange}
          validationErrors={[]}
        />
      </FetcherContext.Provider>
    );
  });

  userEvent.click(screen.getByLabelText(`pim_common.channel`));
  await userEvent.click(screen.getByText(`[print]`));

  expect(handleOperatorChange).toHaveBeenCalledWith({
    context: {locales: ['fr_FR', 'en_US'], scope: 'print'},
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
            context: {locales: [], scope: 'ecommerce'},
          }}
          onChange={handleLocalesChange}
          validationErrors={[]}
        />
      </FetcherContext.Provider>
    );
  });

  userEvent.click(screen.getByLabelText(`akeneo.tailored_export.filters.completeness.locales.label`));
  await userEvent.click(screen.getByText('English'));

  expect(handleLocalesChange).toHaveBeenCalledWith({
    context: {locales: ['en_US'], scope: 'ecommerce'},
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
      </FetcherContext.Provider>
    );
  });

  expect(screen.getByText(operatorErrorMessage)).toBeInTheDocument();
});
