import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
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

let mockedGrantedAcl: string[] = [];
jest.mock('@akeneo-pim-community/legacy-bridge/src/hooks/useSecurity', () => ({
  useSecurity: () => ({
    isGranted: (acl: string) => mockedGrantedAcl.includes(acl),
  }),
}));

beforeEach(() => {
  mockedGrantedAcl = ['pim_enrich_associations_remove', 'pim_enrich_associations_edit'];
});

test('It displays a quantified association row for a product', () => {
  const onChange = jest.fn();
  const onRemove = jest.fn();

  const {getByTitle, getByText, queryByText} = renderWithProviders(
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

  const quantityInput = getByTitle(
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement;

  expect(getByText('Nice bag')).toBeInTheDocument();
  expect(quantityInput.value).toBe('3');
  expect(queryByText('Braided hat')).not.toBeInTheDocument();
});

test('It displays a quantified association row for a product model', () => {
  const onChange = jest.fn();
  const onRemove = jest.fn();

  const {getByTitle, getByText, queryByText} = renderWithProviders(
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

  const quantityInput = getByTitle(
    'pim_enrich.entity.product.module.associations.quantified.quantity'
  ) as HTMLInputElement;

  expect(getByText('Braided hat')).toBeInTheDocument();
  expect(quantityInput.value).toBe('15');
  expect(queryByText('Nice bag')).not.toBeInTheDocument();
});

test('It triggers the onChange event when updating the quantity', () => {
  const onChange = jest.fn();
  const onRemove = jest.fn();

  const {getByTitle} = renderWithProviders(
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

  const quantityInput = getByTitle(
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

  const {getByTitle} = renderWithProviders(
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

  const removeButton = getByTitle('pim_enrich.entity.product.module.associations.remove');
  fireEvent.click(removeButton);

  expect(onChange).not.toBeCalled();
  expect(onRemove).toBeCalled();
});

test('It triggers the onRemove event when the remove button is clicked in compact mode', () => {
  const onChange = jest.fn();
  const onRemove = jest.fn();

  const {getByTitle} = renderWithProviders(
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

  const removeButton = getByTitle('pim_enrich.entity.product.module.associations.remove');
  fireEvent.click(removeButton);

  expect(onChange).not.toBeCalled();
  expect(onRemove).toBeCalled();
});

test('It cannot remove an association when user did not have the ACL', () => {
  mockedGrantedAcl = ['pim_enrich_associations_edit'];

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
          onChange={jest.fn()}
          onRemove={jest.fn()}
        />
      </tbody>
    </table>
  );

  const removeButton = screen.queryByTitle('pim_enrich.entity.product.module.associations.remove');
  expect(removeButton).not.toBeInTheDocument();
});

test('It cannot update the quantity of an association when user did not have the ACL', () => {
  mockedGrantedAcl = [];
  const handleChange = jest.fn();

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
          onChange={handleChange}
          onRemove={jest.fn()}
        />
      </tbody>
    </table>
  );

  const quantityInput = screen.getByTitle('pim_enrich.entity.product.module.associations.quantified.quantity');
  fireEvent.change(quantityInput, {target: {value: '16'}});

  expect(handleChange).not.toBeCalled();
});
