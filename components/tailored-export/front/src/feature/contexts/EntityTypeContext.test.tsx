import {renderHook} from '@testing-library/react-hooks';
import React, {FC} from 'react';
import {EntityTypeProvider, EntityTypeValue, useEntityType} from './EntityTypeContext';

const DefaultProviders: FC<{entityType: EntityTypeValue}> = ({children, entityType}) => (
  <EntityTypeProvider entityType={entityType}>{children}</EntityTypeProvider>
);

const renderHookWithProviders = (hook: () => any, entityType: EntityTypeValue) =>
  renderHook(hook, {wrapper: DefaultProviders, initialProps: {entityType}});

test('I can get product entity type', async () => {
  const {result} = renderHookWithProviders(() => useEntityType(), 'product');

  expect(result.current).toEqual('product');
});

test('I can get product model entity type', async () => {
  const {result} = renderHookWithProviders(() => useEntityType(), 'product_model');

  expect(result.current).toEqual('product_model');
});
