import React, {FunctionComponent, useEffect, useState} from 'react';
import {get} from 'lodash';
import {useCatalogContext, useFetchProductAxisRates} from "../../../../infrastructure/hooks";
import Rate from "../../Rate";
import {useAxesContext} from "../../../context/AxesContext";

const __ = require('oro/translator');

interface AxisRatesOverviewProps {
}

const AxisRatesOverview: FunctionComponent<AxisRatesOverviewProps> = () => {
  const {locale, channel} = useCatalogContext();
  const productAxisRates = useFetchProductAxisRates();
  const [isLoading, setIsLoading] = useState(true);
  const axesContext = useAxesContext();

  useEffect(() => {
    if (productAxisRates === undefined || Object.keys(productAxisRates).length === 0) {
      return;
    }
    setIsLoading(false);
  }, [productAxisRates]);

  // @ts-ignore
  const enrichmentRate = get(productAxisRates, ['enrichment', 'rates', channel, locale], null) as any;
  let consistencyRate: any;
  if (axesContext.axes.includes('consistency')) {
    // @ts-ignore
    consistencyRate = get(productAxisRates, ['consistency', 'rates', channel, locale], null);
  }

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
              {axesContext.axes.includes('consistency') &&
                <li>
                    <span>{__(`akeneo_data_quality_insights.product_evaluation.axis.consistency.title`)}</span>
                    <Rate value={consistencyRate} isLoading={isLoading}/>
                </li>
              }
            </ul>
          </div>
        </div>
      )}
    </>
  );
};

export default AxisRatesOverview;
