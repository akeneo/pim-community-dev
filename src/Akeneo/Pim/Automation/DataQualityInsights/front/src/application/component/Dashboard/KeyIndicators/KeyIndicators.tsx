import React, {Children, FC, ReactElement} from 'react';
import {useFetchKeyIndicators} from '../../../../infrastructure/hooks';
import styled from 'styled-components';
import {keyIndicatorMap} from '../../../../domain';
import {SectionTitle} from "./SectionTitle";

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
      <SectionTitle title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.title'}/>

      <KeyIndicatorContainer>
        {keyIndicators === null && <div data-testid={'dqi-key-indicator-loading'} className="AknLoadingMask"/>}
        {
          keyIndicators !== null && Children.map(children, ((child) => {
            const element = child as ReactElement;

            if (!keyIndicators.hasOwnProperty(element.props.type)) {
              return;
            }

            const keyIndicatorData = keyIndicators[element.props.type];

            return React.cloneElement(
              element,
              {
                type: element.props.type,
                ratio: parseFloat(keyIndicatorData.ratio.toString()),
                total: keyIndicatorData.total,
              }
            );
          }))
        }
      </KeyIndicatorContainer>
    </div>
  );
}

const KeyIndicatorContainer = styled.div`
  display: flex;
  flex-wrap: wrap;
  position: relative;
  min-height: 100px;
`;

export {KeyIndicators};
