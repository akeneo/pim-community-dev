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

const operatorsAndVisiblity = [
  {operator: 'ALL', shouldAppear: false},
  {operator: 'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE', shouldAppear: true},
  {operator: 'GREATER OR EQUALS THAN ON ALL LOCALES', shouldAppear: true},
  {operator: 'LOWER THAN ON ALL LOCALES', shouldAppear: true},
] as const;

test.each(operatorsAndVisiblity)(
  'it displays the locale selector depending on the operator',
  async ({operator, shouldAppear}: {operator: Operator; shouldAppear: boolean}) => {
    await act(async () => {
      renderWithProviders(
        <FetcherContext.Provider value={fetchers}>
          <CompletenessFilter
            operator={operator}
            locales={['fr_FR', 'en_US']}
            onOperatorChange={() => {}}
            onLocalesChange={() => {}}
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
          operator="ALL"
          locales={['fr_FR', 'en_US']}
          onOperatorChange={handleOperatorChange}
          onLocalesChange={() => {}}
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

  expect(handleOperatorChange).toHaveBeenCalledWith('GREATER OR EQUALS THAN ON ALL LOCALES');
});

test('it can  select locales', async () => {
  const handleLocalesChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <CompletenessFilter
          operator="GREATER OR EQUALS THAN ON ALL LOCALES"
          locales={[]}
          onOperatorChange={jest.fn()}
          onLocalesChange={handleLocalesChange}
          validationErrors={[]}
        />
      </FetcherContext.Provider>
    );
  });

  const openDropdownButton = screen.getAllByTitle('pim_common.open')[1];
  userEvent.click(openDropdownButton);
  const greaterThanButton = screen.getByText('English');
  await userEvent.click(greaterThanButton);

  expect(handleLocalesChange).toHaveBeenCalledWith(['en_US']);
});

test('it displays locales validation errors', async () => {
  const localesErrorMessage = 'error with the locales';

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <CompletenessFilter
          operator="GREATER OR EQUALS THAN ON ALL LOCALES"
          locales={[]}
          onOperatorChange={() => {}}
          onLocalesChange={() => {}}
          validationErrors={[
            {
              messageTemplate: localesErrorMessage,
              parameters: {},
              message: '',
              propertyPath: '[locales]',
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
          operator="GREATER OR EQUALS THAN ON ALL LOCALES"
          locales={[]}
          onOperatorChange={() => {}}
          onLocalesChange={() => {}}
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
