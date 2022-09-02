import React, {ReactNode} from 'react';
import {ReloadPreviewProvider} from 'akeneoassetmanager/application/hooks/useReloadPreview';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {FakeConfigProvider} from '../utils/FakeConfigProvider';

const renderWithAssetManagerProviders = (element: ReactNode) =>
  renderWithProviders(
    <FakeConfigProvider>
      <ReloadPreviewProvider>{element}</ReloadPreviewProvider>
    </FakeConfigProvider>
  );

export {renderWithAssetManagerProviders};
