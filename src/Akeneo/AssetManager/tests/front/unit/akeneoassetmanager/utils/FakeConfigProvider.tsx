import React, {ReactNode} from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';
import * as TextDenormalize from 'akeneoassetmanager/domain/model/attribute/type/text';
import * as TextReducer from 'akeneoassetmanager/application/reducer/attribute/type/text';
import * as MediaFileDenormalize from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import * as MediaFileReducer from 'akeneoassetmanager/application/reducer/attribute/type/media-file';
import * as OptionDenormalize from 'akeneoassetmanager/domain/model/attribute/type/option';
import * as OptionReducer from 'akeneoassetmanager/application/reducer/attribute/type/option';
import * as OptionCollectionDenormalize from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import * as NumberReducer from 'akeneoassetmanager/application/reducer/attribute/type/number';
import * as NumberDenormalize from 'akeneoassetmanager/domain/model/attribute/type/number';
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
    option: {
      icon: 'bundles/pimui/images/attribute/icon-select.svg',
      denormalize: OptionDenormalize,
      reducer: OptionReducer,
      view: require('akeneoassetmanager/application/component/attribute/edit/option'),
    },
    option_collection: {
      icon: 'bundles/pimui/images/attribute/icon-multiselect.svg',
      denormalize: OptionCollectionDenormalize,
      reducer: OptionReducer,
      view: require('akeneoassetmanager/application/component/attribute/edit/option'),
    },
    number: {
      icon: 'bundles/pimui/images/attribute/icon-number.svg',
      denormalize: NumberDenormalize,
      reducer: NumberReducer,
      view: require('akeneoassetmanager/application/component/attribute/edit/number.tsx'),
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
