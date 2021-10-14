import React, {Children, FC} from 'react';
import {useFetchKeyIndicators} from '../../../../infrastructure/hooks';
import styled from 'styled-components';
import {keyIndicatorMap} from '../../../../domain';
import {SectionTitle} from './SectionTitle';
import {EmptyKeyIndicators} from './EmptyKeyIndicators';
import {Helper, LockIcon, useTheme} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

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

            const keyIndicatorData = keyIndicators.hasOwnProperty(child.props.type)
              ? keyIndicators[child.props.type]
              : null;

            return React.cloneElement(
              child,
              keyIndicatorData !== null
                ? {
                    ratioGood: parseFloat(keyIndicatorData.ratioGood.toString()),
                    totalToImprove: keyIndicatorData.totalToImprove,
                    extraData: keyIndicatorData?.extraData,
                  }
                : {}
            );
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
