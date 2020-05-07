import React from 'react';
import ReactDOM from 'react-dom';
import {act, getByText, fireEvent, queryByText, getByTitle, getAllByTitle} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {QuantifiedAssociations} from '../../../../Resources/public/js/product/form/quantified-associations/components/QuantifiedAssociations';

jest.mock('@akeneo-pim-community/legacy-bridge/provider/dependencies.ts');
jest.mock('pimui/js/product/form/quantified-associations/hooks/useProducts.ts', () => ({
  useProducts: (identifiers: {products: string[]; product_models: string[]}) => {
    if (0 === identifiers.products.length) return [];
    if (null === identifiers.products[0]) return null;
    return [
      {
        id: 1,
        identifier: 'bag',
        label: 'Nice bag',
        document_type: 'product',
        image: null,
        completeness: 100,
        variant_product_completenesses: null,
      },
      {
        id: 2,
        identifier: 'braided-hat',
        label: 'Braided hat',
        document_type: 'product_model',
        image: {filePath: '/some.jpg'},
        completeness: null,
        variant_product_completenesses: {
          completeChildren: 1,
          totalChildren: 2,
        },
      },
    ];
  },
}));

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

const quantifiedAssociationCollection = {
  PACK: {
    products: [{identifier: 'bag', quantity: '3'}],
    product_models: [{identifier: 'braided-hat', quantity: '15'}],
  },
};

test('It displays quantified association rows for a quantified association collection', async () => {
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <QuantifiedAssociations
            value={quantifiedAssociationCollection}
            associationTypeCode="PACK"
            onAssociationsChange={jest.fn()}
            onOpenPicker={jest.fn()}
          />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  const quantityInputs = getAllByTitle(
    container,
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement[];

  expect(getByText(container, 'Nice bag')).toBeInTheDocument();
  expect(queryByText(container, 'Braided hat')).toBeInTheDocument();
  expect(quantityInputs[0].value).toBe('3');
  expect(quantityInputs.length).toBe(2);
});

test('It displays no rows and a no data information when the quantified association collection is empty', async () => {
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <QuantifiedAssociations
            value={{
              PACK: {
                products: [],
                product_models: [],
              },
            }}
            associationTypeCode="PACK"
            onAssociationsChange={jest.fn()}
            onOpenPicker={jest.fn()}
          />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(queryByText(container, 'Nice bag')).not.toBeInTheDocument();
  expect(queryByText(container, 'Braided hat')).not.toBeInTheDocument();
  expect(getByText(container, 'pim_enrich.entity.product.module.associations.no_data')).toBeInTheDocument();
});

test('It triggers the onChange event when a quantity is changed', async () => {
  const onChange = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <QuantifiedAssociations
            value={quantifiedAssociationCollection}
            associationTypeCode="PACK"
            onAssociationsChange={onChange}
            onOpenPicker={jest.fn()}
          />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  const quantityInputs = getAllByTitle(
    container,
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement[];

  fireEvent.change(quantityInputs[0], {target: {value: '16'}});

  expect(onChange).toBeCalledWith({
    PACK: {
      products: [{identifier: 'bag', quantity: '16'}],
      product_models: [{identifier: 'braided-hat', quantity: '15'}],
    },
  });
});

test('It displays no rows and a placeholder when the quantified association collection is loading', async () => {
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <QuantifiedAssociations
            value={{
              PACK: {
                //The useProducts mock defined above will simulate loading
                products: [{identifier: null, quantity: null}],
                product_models: [],
              },
            }}
            associationTypeCode="PACK"
            onAssociationsChange={jest.fn()}
            onOpenPicker={jest.fn()}
          />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(container.querySelector('.AknLoadingPlaceHolderContainer')).toBeInTheDocument();
});
