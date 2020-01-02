import React, {FunctionComponent} from 'react';
import {ContextualRate} from '../../ContextualRate';
import {Axis} from '../../../../domain';
import {useCatalogContext, useFetchProductAxisRates} from "../../../../infrastructure/hooks";

const __ = require('oro/translator');

interface AxisRatesOverviewProps {

}

const AxisRatesOverview: FunctionComponent<AxisRatesOverviewProps> = () => {
  const {locale, channel} = useCatalogContext();
  const data = useFetchProductAxisRates();

  return (
    <>
      {channel && locale && data && (
        <div className="AknColumn-block AknDataQualityInsights">
          <div className="AknColumn-subtitle">{__('akeneo_data_quality_insights.title')}</div>
          <div className="AknColumn-value">
            <ul>
              {data && Object.values(data).map((axis: Axis) => {
                return (
                  <li key={axis.code}>
                    <span>{__(`akeneo_data_quality_insights.product_evaluation.axis.${axis.code}.title`)}</span>
                    <ContextualRate rates={axis.rates} channel={channel} locale={locale}/>
                  </li>
                );
              })}
            </ul>
          </div>
        </div>
      )}
    </>
  );
};

export default AxisRatesOverview;
