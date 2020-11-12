import React from 'react';
import {renderWithProviders} from '../../../../../test-utils';
import {Status} from '../../../../../src/rules.constants';
import {ProductAndProductModelCount} from '../../../../../src/pages/EditRules/components/ProductAndProductModelCount';

describe('ProductAndProductModelCount', () => {
  test('it should render in complete mode', () => {
    // Given
    const status = Status.COMPLETE;
    // When
    const {getByText} = renderWithProviders(
      <ProductAndProductModelCount productCount={10} productModelCount={15} status={status} />,
      {legacy: true}
    );
    // Then
    expect(
      getByText('pimee_catalog_rule.form.edit.products_count.products_and_product_models')
    ).toBeInTheDocument();
  });
  test('it should render in error mode', () => {
    // Given
    const status = Status.ERROR;
    // When
    const {getByText} = renderWithProviders(
      <ProductAndProductModelCount productCount={-1} productModelCount={-1} status={status} />,
      {legacy: true}
    );
    // Then
    expect(
      getByText('pimee_catalog_rule.form.edit.products_count.error')
    ).toBeInTheDocument();
  });
  test('it should render in pending mode', () => {
    // Given
    const status = Status.PENDING;
    // When
    const {getByText} = renderWithProviders(
      <ProductAndProductModelCount productCount={-1} productModelCount={-1} status={status} />,
      {legacy: true}
    );
    // Then
    expect(
      getByText('pimee_catalog_rule.form.edit.products_count.pending')
    ).toBeInTheDocument();
  });
});
