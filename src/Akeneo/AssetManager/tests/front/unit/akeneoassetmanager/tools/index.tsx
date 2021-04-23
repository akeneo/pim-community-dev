import React, {ReactNode} from 'react';
import {ReloadPreviewProvider} from 'akeneoassetmanager/application/hooks/useReloadPreview';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

const renderWithAssetManagerProviders = (element: ReactNode) =>
  renderWithProviders(<ReloadPreviewProvider>{element}</ReloadPreviewProvider>);

export {renderWithAssetManagerProviders};
