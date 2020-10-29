import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  screen,
  renderWithProviders,
  waitForElementToBeRemoved,
  fireEvent,
} from '../../../../../../../test-utils';
import {
  AssociationType,
  AssociationValue,
} from '../../../../../../../src/models';
import userEvent from '@testing-library/user-event';
import {AssociationTypesSelector} from '../../../../../../../src/pages/EditRules/components/actions/association/AssociationTypesSelector';

const associationTypes: AssociationType[] = [
  {
    code: 'X_SELL',
    is_quantified: false,
    is_two_way: false,
    labels: {
      en_US: 'X sell',
      fr_FR: 'Vente croisee',
    },
    meta: {id: 42},
  },
  {
    code: 'PACK',
    is_quantified: false,
    is_two_way: false,
    labels: {
      en_US: 'Pack',
      fr_FR: 'Paques',
    },
    meta: {id: 43},
  },
];

const response = (request: Request) => {
  if (request.url.includes('pim_enrich_associationtype_rest_index')) {
    return Promise.resolve(JSON.stringify(associationTypes));
  }
  throw new Error(`The "${request.url}" url is not mocked.`);
};

describe('SetAssociationsActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display the set associations action line, switch type and delete it', async () => {
    fetchMock.mockResponse(response);

    const onChange = jest.fn();
    const value: AssociationValue = {
      X_SELL: {
        products: ['product_1', 'product_2'],
        product_models: [],
      },
      PACK: {
        product_models: ['product_model_1', 'product_model_2'],
        groups: ['group_1'],
      },
    };

    renderWithProviders(
      <AssociationTypesSelector
        value={value}
        onChange={onChange}
        required={false}
      />,
      {all: true}
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

    // Switch type test
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

    // Delete test
    await act(async () => {
      userEvent.click(
        await screen.findByTestId('delete-association-type-button-PACK-groups')
      );
    });
    expect(
      screen.queryByTestId('association-type-selector-PACK-groups')
    ).not.toBeInTheDocument();
    expect(onChange).toHaveBeenLastCalledWith({
      X_SELL: {
        products: ['product_1', 'product_2'],
        product_models: [],
      },
      PACK: {
        product_models: ['product_model_1', 'product_model_2'],
      },
    });
  });

  it('should display products', async () => {
    fetchMock.mockResponse(response);

    const onChange = jest.fn();
    const value: AssociationValue = {
      X_SELL: {
        products: ['product_1', 'product_2'],
      },
    };

    renderWithProviders(
      <AssociationTypesSelector
        value={value}
        onChange={onChange}
        required={false}
      />,
      {all: true}
    );

    await waitForElementToBeRemoved(() =>
      document.querySelector('img[alt="pim_common.loading"]')
    ).then(async () => {
      expect(
        await screen.findByTestId('product-or-product-model-selector-product_1')
      ).toBeInTheDocument();
      expect(
        await screen.findByTestId('product-or-product-model-selector-product_2')
      ).toBeInTheDocument();

      // When
      userEvent.click(await screen.findByTestId('association-types-selector'));
      expect(
        (await screen.findByTestId('association-types-selector')).children
          .length
      ).toBeGreaterThan(1);
      fireEvent.change(
        await screen.findByTestId('association-types-selector'),
        {
          target: {value: 'products'},
        }
      );

      // Then
      expect(
        screen.getByTestId('association-type-selector-PACK-products')
      ).toBeInTheDocument();
      expect(
        screen.queryByTestId('product-or-product-model-selector-product_1')
      ).not.toBeInTheDocument();
      expect(onChange).toHaveBeenLastCalledWith({
        ...value,
        PACK: {products: []},
      });
    });
  });

  it('should display product models', async () => {
    fetchMock.mockResponse(response);

    const onChange = jest.fn();
    const value: AssociationValue = {
      X_SELL: {
        product_models: ['product_model_1', 'product_model_2'],
      },
    };

    renderWithProviders(
      <AssociationTypesSelector
        value={value}
        onChange={onChange}
        required={false}
      />,
      {all: true}
    );

    await waitForElementToBeRemoved(() =>
      document.querySelector('img[alt="pim_common.loading"]')
    ).then(async () => {
      expect(
        await screen.findByTestId(
          'product-or-product-model-selector-product_model_1'
        )
      ).toBeInTheDocument();
      expect(
        await screen.findByTestId(
          'product-or-product-model-selector-product_model_2'
        )
      ).toBeInTheDocument();

      // When
      userEvent.click(await screen.findByTestId('association-types-selector'));
      expect(
        (await screen.findByTestId('association-types-selector')).children
          .length
      ).toBeGreaterThan(1);
      fireEvent.change(
        await screen.findByTestId('association-types-selector'),
        {
          target: {value: 'product_models'},
        }
      );

      // Then
      expect(
        screen.getByTestId('association-type-selector-PACK-product_models')
      ).toBeInTheDocument();
      expect(
        screen.queryByTestId(
          'product-or-product-model-selector-product_model_1'
        )
      ).not.toBeInTheDocument();
      expect(onChange).toHaveBeenLastCalledWith({
        ...value,
        PACK: {product_models: []},
      });
    });
  });

  it('should display groups', async () => {
    fetchMock.mockResponse(response);

    const onChange = jest.fn();
    const value: AssociationValue = {
      X_SELL: {
        groups: ['group_1', 'group_2'],
      },
    };

    renderWithProviders(
      <AssociationTypesSelector
        value={value}
        onChange={onChange}
        required={false}
      />,
      {all: true}
    );

    await waitForElementToBeRemoved(() =>
      document.querySelector('img[alt="pim_common.loading"]')
    ).then(async () => {
      expect(
        await screen.findByTestId('group-selector-group_1')
      ).toBeInTheDocument();
      expect(
        await screen.findByTestId('group-selector-group_2')
      ).toBeInTheDocument();

      // When
      userEvent.click(await screen.findByTestId('association-types-selector'));
      expect(
        (await screen.findByTestId('association-types-selector')).children
          .length
      ).toBeGreaterThan(1);
      fireEvent.change(
        await screen.findByTestId('association-types-selector'),
        {
          target: {value: 'groups'},
        }
      );

      // Then
      expect(
        screen.getByTestId('association-type-selector-PACK-groups')
      ).toBeInTheDocument();
      expect(
        screen.queryByTestId('group-selector-group_1')
      ).not.toBeInTheDocument();
      expect(onChange).toHaveBeenLastCalledWith({
        ...value,
        PACK: {groups: []},
      });
    });
  });
});
