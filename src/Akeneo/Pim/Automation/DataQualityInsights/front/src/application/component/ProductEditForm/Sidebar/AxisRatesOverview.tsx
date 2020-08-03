import React, {FunctionComponent, useEffect, useState} from 'react';
import {get} from 'lodash';
import {useCatalogContext, useFetchProductAxisRates} from "../../../../infrastructure/hooks";
import Rate from '@akeneo-pim-community/data-quality-insights/src/application/component/Rate';

const __ = require('oro/translator');

interface AxisRatesOverviewProps {

}

const AxisRatesOverview: FunctionComponent<AxisRatesOverviewProps> = () => {
  const {locale, channel} = useCatalogContext();
  const productAxisRates = useFetchProductAxisRates();
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (productAxisRates === undefined || Object.keys(productAxisRates).length === 0) {
      return;
    }
    setIsLoading(false);
  }, [productAxisRates]);

  // @ts-ignore
  let enrichmentRate = get(productAxisRates, ['enrichment', 'rates', channel, locale], null);
  // @ts-ignore
  let consistencyRate = get(productAxisRates, ['consistency', 'rates', channel, locale], null);

  return (
    <>
      {channel && locale && (
        <div className="AknColumn-block AknDataQualityInsights">
          <div className="AknColumn-subtitle">{__('akeneo_data_quality_insights.title')}</div>
          <div className="AknColumn-value">
            <ul>
              <li>
                <span>{__(`akeneo_data_quality_insights.product_evaluation.axis.enrichment.title`)}</span>
                <Rate value={enrichmentRate} isLoading={isLoading}/>
              </li>
              <li>
                <span>{__(`akeneo_data_quality_insights.product_evaluation.axis.consistency.title`)}</span>
                <Rate value={consistencyRate} isLoading={isLoading}/>
              </li>
            </ul>
          </div>
        </div>
      )}
    </>
  );
};

export default AxisRatesOverview;
