import React, {Children, FC} from 'react';
import styled from 'styled-components';

import {useTranslate} from '@akeneo-pim-community/shared';
import {Helper, LockIcon, useTheme} from 'akeneo-design-system';

import {useFetchKeyIndicators} from '../../../../infrastructure/hooks';
import {CountsByProductType, keyIndicatorMap, makeCountsByProductType} from '../../../../domain';

import {SectionTitle} from './SectionTitle';
import {EmptyKeyIndicators} from './EmptyKeyIndicators';

const featureFlags = require('pim/feature-flags');

type Props = {
  channel: string;
  locale: string;
  family: string | null;
  category: string | null;
};

const KeyIndicators: FC<Props> = ({children, channel, locale, family, category}) => {
  const keyIndicators: keyIndicatorMap | null = useFetchKeyIndicators(channel, locale, family, category);
  const theme = useTheme();
  const translate = useTranslate();

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

      <KeyIndicatorContainer>
        {keyIndicators === null && <div data-testid={'dqi-key-indicator-loading'} className="AknLoadingMask" />}
        {keyIndicators !== null && Object.keys(keyIndicators).length === 0 && <EmptyKeyIndicators />}
        {keyIndicators !== null &&
          Object.keys(keyIndicators).length > 0 &&
          Children.map(children, child => {
            if (!React.isValidElement(child)) {
              return;
            }

            const counts: CountsByProductType = keyIndicators.hasOwnProperty(child.props.type)
              ? keyIndicators[child.props.type]
              : makeCountsByProductType();

            return React.cloneElement(child, {
              counts,
            });
          })}
      </KeyIndicatorContainer>
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
