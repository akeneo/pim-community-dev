import React, {FunctionComponent} from 'react';
import {get} from 'lodash';
import {useCatalogContext, useFetchProductAxisRates} from "../../../../infrastructure/hooks";
import Rate from "../../Rate";

const __ = require('oro/translator');

interface AxisRatesOverviewProps {

}

const AxisRatesOverview: FunctionComponent<AxisRatesOverviewProps> = () => {
  const {locale, channel} = useCatalogContext();
  const productEvaluation = useFetchProductAxisRates();

  return (
    <>
      {channel && locale && productEvaluation && (
        <div className="AknColumn-block AknDataQualityInsights">
          <div className="AknColumn-subtitle">{__('akeneo_data_quality_insights.title')}</div>
          <div className="AknColumn-value">
            <ul>
              {productEvaluation && Object.entries(productEvaluation).map(([axisCode, axisEvaluation]) => {
                const axisRate = get(axisEvaluation, [channel, locale, 'rate']);
                return (
                  <li key={axisCode}>
                    <span>{__(`akeneo_data_quality_insights.product_evaluation.axis.${axisCode}.title`)}</span>
                    <Rate value={axisRate} />
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
