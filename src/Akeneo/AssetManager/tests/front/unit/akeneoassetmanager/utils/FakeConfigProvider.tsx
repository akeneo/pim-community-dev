import React, {ReactNode} from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';
import * as TextDenormalize from 'akeneoassetmanager/domain/model/attribute/type/text';
import * as TextReducer from 'akeneoassetmanager/application/reducer/attribute/type/text';
import * as MediaFileDenormalize from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import * as MediaFileReducer from 'akeneoassetmanager/application/reducer/attribute/type/media-file';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

type FakeConfigProviderProps = {
  children: ReactNode;
};

const fakeConfig = {
  value: {
    text: {
      view: require('akeneoassetmanager/application/component/asset/edit/enrich/data/text.tsx'),
    },
    option: {
      view: require('akeneoassetmanager/application/component/asset/edit/enrich/data/option.tsx'),
      filter: require('akeneoassetmanager/application/component/asset/list/filter/option.tsx'),
    },
  },
  attribute: {
    text: {
      icon: 'bundles/pimui/images/attribute/icon-text.svg',
      denormalize: TextDenormalize,
      reducer: TextReducer,
      view: require('akeneoassetmanager/application/component/attribute/edit/text'),
    },
    media_file: {
      icon: 'bundles/pimui/images/attribute/icon-mediafile.svg',
      denormalize: MediaFileDenormalize,
      reducer: MediaFileReducer,
      view: require('akeneoassetmanager/application/component/attribute/edit/media-file'),
    },
  },
  sidebar: {
    akeneo_asset_manager_asset_family_edit: {
      tabs: {
        attribute: {
          label: 'First tab',
          view: require('akeneoassetmanager/application/component/asset-family/edit/attribute.tsx'),
        },
      },
    },
  },
};

const FakeConfigProvider = ({children}: FakeConfigProviderProps) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <ConfigProvider config={fakeConfig}>{children}</ConfigProvider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {FakeConfigProvider, fakeConfig};
