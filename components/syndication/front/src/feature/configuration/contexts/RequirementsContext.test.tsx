import {renderHook} from '@testing-library/react-hooks';
import {RequirementCollection} from '../models';
import React, {FC} from 'react';
import {RequirementsProvider, useRequirement} from './RequirementsContext';

const DefaultProviders: FC<{Requirements: RequirementCollection}> = ({children, Requirements}) => (
  <RequirementsProvider requirements={Requirements}>{children}</RequirementsProvider>
);

const renderHookWithProviders = (hook: () => any, Requirements: RequirementCollection) =>
  renderHook(hook, {wrapper: DefaultProviders, initialProps: {Requirements}});

test('I can get sku requirement', async () => {
  const {result} = renderHookWithProviders(
    () => useRequirement('sku'),
    [
      {
        code: 'sku',
        required: true,
        type: 'string',
      },
    ]
  );

  expect(result.current).toEqual({
    code: 'sku',
    required: true,
    type: 'string',
  });
});
