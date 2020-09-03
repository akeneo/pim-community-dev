import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  screen,
  renderWithProviders,
  waitForElementToBeRemoved,
} from '../../../../../../test-utils';
import { SetAssociationsAction } from '../../../../../../src/models/actions';
import { locales, scopes } from '../../../../factories';
import { AssociationType } from '../../../../../../src/models';
import userEvent from '@testing-library/user-event';
import { SetAssociationsActionLine } from '../../../../../../src/pages/EditRules/components/actions/SetAssociationsActionLine';

const associationTypes: AssociationType[] = [
  {
    code: 'X_SELL',
    is_quantified: false,
    is_two_way: false,
    labels: {
      en_US: 'X sell',
      fr_FR: 'Vente croisee',
    },
    meta: { id: 42 },
  },
  {
    code: 'PACK',
    is_quantified: false,
    is_two_way: false,
    labels: {
      en_US: 'Pack',
      fr_FR: 'Paques',
    },
    meta: { id: 43 },
  },
];

describe('SetAssociationsActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display the set categories action line, and switch tree', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_associationtype_rest_index')) {
        return Promise.resolve(JSON.stringify(associationTypes));
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const action: SetAssociationsAction = {
      type: 'set',
      field: 'associations',
      value: {
        X_SELL: {
          products: ['product_1', 'product_2'],
          product_models: [],
        },
        PACK: {
          product_models: ['product_model_1', 'product_model_2'],
          groups: ['group_1'],
        },
      },
    };
    const defaultValues = {
      content: {
        actions: [action],
      },
    };

    const toRegister = [
      { name: 'content.actions[0].value', type: 'custom' },
      { name: 'content.actions[0].field', type: 'custom' },
      { name: 'content.actions[0].type', type: 'custom' },
    ];

    renderWithProviders(
      <SetAssociationsActionLine
        action={action}
        lineNumber={0}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={jest.fn()}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    await waitForElementToBeRemoved(() =>
      document.querySelector('img[alt="pim_common.loading"]')
    ).then(() => {
      expect(
        screen.getByTestId('association-type-selector-X_SELL-products')
      ).toBeInTheDocument();
      expect(
        screen.getByTestId('association-type-selector-X_SELL-product_models')
      ).toBeInTheDocument();
      expect(
        screen.getByTestId('association-type-selector-PACK-product_models')
      ).toBeInTheDocument();
      expect(
        screen.getByTestId('association-type-selector-PACK-groups')
      ).toBeInTheDocument();
      expect(
        screen.getByText(
          'pimee_catalog_rule.form.edit.actions.set_associations.select_title.products'
        )
      ).toBeInTheDocument();
    });
    await act(async () => {
      userEvent.click(
        await screen.findByTestId('association-type-selector-PACK-groups')
      );
    });
    expect(
      screen.getByText(
        'pimee_catalog_rule.form.edit.actions.set_associations.select_title.groups'
      )
    ).toBeInTheDocument();
  });
});
