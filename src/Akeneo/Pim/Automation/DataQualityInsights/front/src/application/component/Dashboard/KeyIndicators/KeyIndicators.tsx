import React, {Children, FC} from 'react';
import {useFetchKeyIndicators} from '../../../../infrastructure/hooks';
import styled from 'styled-components';
import {keyIndicatorMap} from '../../../../domain';
import {SectionTitle} from './SectionTitle';
import {EmptyKeyIndicators} from './EmptyKeyIndicators';

type Props = {
  channel: string;
  locale: string;
  family: string | null;
  category: string | null;
};

const KeyIndicators: FC<Props> = ({children, channel, locale, family, category}) => {
  const keyIndicators: keyIndicatorMap | null = useFetchKeyIndicators(channel, locale, family, category);

  return (
    <div>
      <SectionTitle title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.title'} />

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

export {KeyIndicators};
