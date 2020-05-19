import React from 'react';
import ReactDOM from 'react-dom';
import {act, getByText, fireEvent, queryByText, getByTitle} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {QuantifiedAssociationRow} from '../../../../Resources/public/js/product/form/quantified-associations/components/QuantifiedAssociationRow';
import {Product, ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models';

jest.mock('legacy-bridge/provider/dependencies.ts');

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

const product: Product = {
  id: 1,
  identifier: 'bag',
  label: 'Nice bag',
  document_type: ProductType.Product,
  image: null,
  completeness: 100,
  variant_product_completenesses: null,
};

const productModel: Product = {
  id: 2,
  identifier: 'braided-hat',
  label: 'Braided hat',
  document_type: ProductType.ProductModel,
  image: {filePath: '/some.jpg', originalFileName: 'some.jpg'},
  completeness: null,
  variant_product_completenesses: {
    completeChildren: 1,
    totalChildren: 2,
  },
};

test('It displays a quantified association row for a product', async () => {
  const onChange = jest.fn();
  const onRowDelete = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <table>
            <tbody>
              <QuantifiedAssociationRow
                row={{
                  productType: ProductType.Product,
                  associationTypeCode: 'PACK',
                  quantity: 3,
                  identifier: 'bag',
                  product: product,
                }}
                onChange={onChange}
                onRowDelete={onRowDelete}
              />
            </tbody>
          </table>
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  const quantityInput = getByTitle(
    container,
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement;

  expect(getByText(container, 'Nice bag')).toBeInTheDocument();
  expect(quantityInput.value).toBe('3');
  expect(queryByText(container, 'Braided hat')).not.toBeInTheDocument();
});

test('It displays a quantified association row for a product model', async () => {
  const onChange = jest.fn();
  const onRowDelete = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <table>
            <tbody>
              <QuantifiedAssociationRow
                row={{
                  productType: ProductType.ProductModel,
                  associationTypeCode: 'PACK',
                  quantity: 15,
                  identifier: 'braided-hat',
                  product: productModel,
                }}
                onChange={onChange}
                onRowDelete={onRowDelete}
              />
            </tbody>
          </table>
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  const quantityInput = getByTitle(
    container,
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement;

  expect(getByText(container, 'Braided hat')).toBeInTheDocument();
  expect(quantityInput.value).toBe('15');
  expect(queryByText(container, 'Nice bag')).not.toBeInTheDocument();
});

test('It triggers the onChange event when updating the quantity', async () => {
  const onChange = jest.fn();
  const onRowDelete = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <table>
            <tbody>
              <QuantifiedAssociationRow
                row={{
                  productType: ProductType.ProductModel,
                  associationTypeCode: 'PACK',
                  quantity: 15,
                  identifier: 'braided-hat',
                  product: productModel,
                }}
                onChange={onChange}
                onRowDelete={onRowDelete}
              />
            </tbody>
          </table>
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  const quantityInput = getByTitle(
    container,
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement;

  fireEvent.change(quantityInput, {target: {value: '16'}});

  expect(onChange).toBeCalledWith({
    productType: ProductType.ProductModel,
    associationTypeCode: 'PACK',
    quantity: 16,
    identifier: 'braided-hat',
    product: productModel,
  });
});

test('It triggers the onRowDelete event when the remove button is clicked', async () => {
  const onChange = jest.fn();
  const onRowDelete = jest.fn();

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <table>
            <tbody>
              <QuantifiedAssociationRow
                row={{
                  productType: ProductType.ProductModel,
                  associationTypeCode: 'PACK',
                  quantity: 15,
                  identifier: 'braided-hat',
                  product: productModel,
                }}
                onChange={onChange}
                onRowDelete={onRowDelete}
              />
            </tbody>
          </table>
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  const removeButton = getByTitle(container, 'pim_enrich.entity.product.module.associations.remove');
  fireEvent.click(removeButton);

  expect(onChange).not.toBeCalled();
  expect(onRowDelete).toBeCalled();
});
