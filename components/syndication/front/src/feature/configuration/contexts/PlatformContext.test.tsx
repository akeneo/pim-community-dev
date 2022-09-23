import {renderHook} from '@testing-library/react-hooks';
import React, {FC} from 'react';
import {PlatformProvider, PlatformValue, usePlatform} from './PlatformContext';

const DefaultProviders: FC<{platform: PlatformValue}> = ({children, platform}) => (
  <PlatformProvider platform={platform}>{children}</PlatformProvider>
);

const renderHookWithProviders = (hook: () => any, platform: PlatformValue) =>
  renderHook(hook, {wrapper: DefaultProviders, initialProps: {platform}});

test('I can get platform code', async () => {
  const {result} = renderHookWithProviders(() => usePlatform(), 'amazon');

  expect(result.current).toEqual('amazon');
});

test('I can get another platform', async () => {
  const {result} = renderHookWithProviders(() => usePlatform(), 'ebay');

  expect(result.current).toEqual('ebay');
});
