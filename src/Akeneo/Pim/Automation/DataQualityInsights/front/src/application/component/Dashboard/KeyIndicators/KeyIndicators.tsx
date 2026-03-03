import React, {FC} from 'react';
import styled from 'styled-components';

import {useTranslate} from '@akeneo-pim-community/shared';
import {Helper, LockIcon, useTheme} from 'akeneo-design-system';

import {useFetchKeyIndicators} from '../../../../infrastructure/hooks';
import {
  isKeyIndicatorProducts,
  KeyIndicatorAttributes,
  KeyIndicatorMap,
  KeyIndicatorProducts,
} from '../../../../domain';

import {SectionTitle} from './SectionTitle';
import {EmptyKeyIndicators} from './EmptyKeyIndicators';
import {AttributesKeyIndicatorLinkCallback, ProductsKeyIndicatorLinkCallback} from '../../../user-actions';
import {KeyIndicatorAboutProducts} from './KeyIndicatorAboutProducts';
import {KeyIndicatorAboutAttributes} from './KeyIndicatorAboutAttributes';

const featureFlags = require('pim/feature-flags');

interface KeyIndicatorAboutProductsDescriptor {
  titleI18nKey: string;
  followResults: ProductsKeyIndicatorLinkCallback;
  icon: React.ReactNode;
}

interface KeyIndicatorAboutAttributesDescriptor {
  titleI18nKey: string;
  followResults: AttributesKeyIndicatorLinkCallback;
  icon: React.ReactNode;
}

export type KeyIndicatorDescriptors = {
  [code in KeyIndicatorProducts]?: KeyIndicatorAboutProductsDescriptor;
} & {
  [code in KeyIndicatorAttributes]?: KeyIndicatorAboutAttributesDescriptor;
};

type Props = {
  channel: string;
  locale: string;
  family: string | null;
  category: string | null;
  keyIndicatorDescriptors: KeyIndicatorDescriptors;
};

const KeyIndicators: FC<Props> = ({channel, locale, family, category, keyIndicatorDescriptors}) => {
  const countsMap: KeyIndicatorMap | null = useFetchKeyIndicators(channel, locale, family, category);
  const theme = useTheme();
  const translate = useTranslate();

  let keyIndicatorContents: React.ReactNode = (function () {
    if (countsMap === null) {
      return <div data-testid={'dqi-key-indicator-loading'} className="AknLoadingMask" />;
    }
    const codes = Object.keys(countsMap) as (KeyIndicatorProducts | KeyIndicatorAttributes)[];
    if (codes.length === 0) {
      return <EmptyKeyIndicators />;
    }
    return codes.map((code: KeyIndicatorProducts | KeyIndicatorAttributes) => {
      if (isKeyIndicatorProducts(code)) {
        const {[code]: descriptor} = keyIndicatorDescriptors;
        if (!descriptor) {
          return null;
        }
        const {titleI18nKey, followResults} = descriptor;

        return (
          <KeyIndicatorAboutProducts
            key={code}
            type={code}
            title={titleI18nKey}
            counts={countsMap[code]!}
            followResults={followResults}
          >
            {descriptor.icon}
          </KeyIndicatorAboutProducts>
        );
      }

      const {[code]: descriptor} = keyIndicatorDescriptors;
      if (!descriptor) {
        return null;
      }
      const {titleI18nKey, followResults} = descriptor;

      return (
        <KeyIndicatorAboutAttributes
          key={code}
          type={code}
          title={titleI18nKey}
          counts={countsMap[code]!}
          followResults={followResults}
        >
          {descriptor.icon}
        </KeyIndicatorAboutAttributes>
      );
    });
  })();

  return (
    <div>
      <SectionTitle title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.title'}>
        {featureFlags.isEnabled('free_trial') && (
          <LockIconContainer>
            <LockIcon size={16} color={theme.color.blue100} />
          </LockIconContainer>
        )}
      </SectionTitle>

      {featureFlags.isEnabled('free_trial') && (
        <Helper level="info">{translate('free_trial.dqi.dashboard.helper')}</Helper>
      )}

      <KeyIndicatorContainer>{keyIndicatorContents}</KeyIndicatorContainer>
    </div>
  );
};

const KeyIndicatorContainer = styled.div`
  display: flex;
  flex-wrap: wrap;
  position: relative;
  min-height: 100px;
`;

const LockIconContainer = styled.div`
  border: 1px solid #4ca8e0;
  border-radius: 4px;
  background: #f0f7fc;
  height: 24px;
  width: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 10px;
`;

export {KeyIndicators};
