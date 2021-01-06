import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {QuantifiedAssociationRow} from '../../../../Resources/public/js/product/form/quantified-associations/components/QuantifiedAssociationRow';
import {Product, ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

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

test('It displays a quantified association row for a product', () => {
  const onChange = jest.fn();
  const onRemove = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <QuantifiedAssociationRow
          row={{
            productType: ProductType.Product,
            quantifiedLink: {quantity: 3, identifier: 'bag'},
            product: product,
            errors: [],
          }}
          parentQuantifiedLink={undefined}
          onChange={onChange}
          onRemove={onRemove}
        />
      </tbody>
    </table>
  );

  const quantityInput = screen.getByTitle(
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement;

  expect(screen.getByText('Nice bag')).toBeInTheDocument();
  expect(quantityInput.value).toBe('3');
  expect(screen.queryByText('Braided hat')).not.toBeInTheDocument();
});

test('It displays errors for a quantified association row', () => {
  const error = {
    messageTemplate: 'An error occured',
    parameters: {},
    message: 'An error occured',
    propertyPath: 'quantity',
    invalidValue: 'NaN',
  };

  renderWithProviders(
    <table>
      <tbody>
        <QuantifiedAssociationRow
          row={{
            productType: ProductType.Product,
            quantifiedLink: {quantity: 3, identifier: 'bag'},
            product: product,
            errors: [error, {...error, messageTemplate: 'Another one'}],
          }}
          parentQuantifiedLink={undefined}
          onChange={jest.fn()}
          onRemove={jest.fn()}
        />
      </tbody>
    </table>
  );

  expect(screen.getByText('Nice bag')).toBeInTheDocument();
  expect(screen.getByText('An error occured')).toBeInTheDocument();
  expect(screen.getByText('Another one')).toBeInTheDocument();
});

test('It displays a quantified association row for a product model', () => {
  const onChange = jest.fn();
  const onRemove = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <QuantifiedAssociationRow
          row={{
            productType: ProductType.ProductModel,
            quantifiedLink: {quantity: 15, identifier: 'braided-hat'},
            product: productModel,
            errors: [],
          }}
          parentQuantifiedLink={undefined}
          onChange={onChange}
          onRemove={onRemove}
        />
      </tbody>
    </table>
  );

  const quantityInput = screen.getByTitle(
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement;

  expect(screen.getByText('Braided hat')).toBeInTheDocument();
  expect(quantityInput.value).toBe('15');
  expect(screen.queryByText('Nice bag')).not.toBeInTheDocument();
});

test('It triggers the onChange event when updating the quantity', () => {
  const onChange = jest.fn();
  const onRemove = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <QuantifiedAssociationRow
          row={{
            productType: ProductType.ProductModel,
            quantifiedLink: {quantity: 15, identifier: 'braided-hat'},
            product: productModel,
            errors: [],
          }}
          parentQuantifiedLink={undefined}
          onChange={onChange}
          onRemove={onRemove}
        />
      </tbody>
    </table>
  );

  const quantityInput = screen.getByTitle(
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement;

  fireEvent.change(quantityInput, {target: {value: '16'}});

  expect(onChange).toBeCalledWith({
    productType: ProductType.ProductModel,
    quantifiedLink: {quantity: 16, identifier: 'braided-hat'},
    product: productModel,
    errors: [],
  });

  fireEvent.change(quantityInput, {target: {value: '1000000000000000000000'}});

  expect(onChange).toBeCalledWith({
    productType: ProductType.ProductModel,
    quantifiedLink: {quantity: 16, identifier: 'braided-hat'},
    product: productModel,
    errors: [],
  });

  fireEvent.change(quantityInput, {target: {value: 'NotANumber'}});

  expect(onChange).toBeCalledWith({
    productType: ProductType.ProductModel,
    quantifiedLink: {quantity: 1, identifier: 'braided-hat'},
    product: productModel,
    errors: [],
  });
});

test('It triggers the onRemove event when the remove button is clicked', () => {
  const onChange = jest.fn();
  const onRemove = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <QuantifiedAssociationRow
          row={{
            productType: ProductType.ProductModel,
            quantifiedLink: {quantity: 15, identifier: 'braided-hat'},
            product: productModel,
            errors: [],
          }}
          parentQuantifiedLink={undefined}
          onChange={onChange}
          onRemove={onRemove}
        />
      </tbody>
    </table>
  );

  const removeButton = screen.getByTitle('pim_enrich.entity.product.module.associations.remove');
  fireEvent.click(removeButton);

  expect(onChange).not.toBeCalled();
  expect(onRemove).toBeCalled();
});

test('It triggers the onRemove event when the remove button is clicked in compact mode', () => {
  const onChange = jest.fn();
  const onRemove = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <QuantifiedAssociationRow
          row={{
            productType: ProductType.ProductModel,
            quantifiedLink: {quantity: 15, identifier: 'braided-hat'},
            product: productModel,
            errors: [],
          }}
          isCompact={true}
          parentQuantifiedLink={undefined}
          onChange={onChange}
          onRemove={onRemove}
        />
      </tbody>
    </table>
  );

  const removeButton = screen.getByTitle('pim_enrich.entity.product.module.associations.remove');
  fireEvent.click(removeButton);

  expect(onChange).not.toBeCalled();
  expect(onRemove).toBeCalled();
});
