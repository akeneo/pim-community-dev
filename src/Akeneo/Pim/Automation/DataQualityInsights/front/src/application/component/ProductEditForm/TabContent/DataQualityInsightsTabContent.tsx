import React, {Fragment, FunctionComponent} from 'react';
import {get as _get} from 'lodash';
import AxisEvaluation from "./DataQualityInsights/AxisEvaluation";
import {useCatalogContext, useFetchProductDataQualityEvaluation} from "../../../../infrastructure/hooks";
import {Evaluation} from "../../../../domain";
import TabContentWithPortalDecorator from "./TabContentWithPortalDecorator";
import {DATA_QUALITY_INSIGHTS_TAB_NAME} from "../../../constant";

export const CONTAINER_ELEMENT_ID = 'data-quality-insights-product-tab-content';

export interface DataQualityInsightsTabContentProps {}

const BaseDataQualityInsightsTabContent: FunctionComponent<DataQualityInsightsTabContentProps> = () => {
  const {locale, channel} = useCatalogContext();
  const productEvaluation = useFetchProductDataQualityEvaluation();

  return (
    <>
      {locale && channel && productEvaluation && (
        <>
        {Object.entries(productEvaluation).map(([code, axisEvaluationData]) => {
          const axisEvaluation: Evaluation = _get(axisEvaluationData, [channel, locale], {
            rate: undefined,
            recommendations: [],
            rates: [],
          });

          return (
            <Fragment key={`axis-${code}`} >
              {axisEvaluation && (
                <AxisEvaluation evaluation={axisEvaluation} axis={code} />
              )}
            </Fragment>
          );
        })}
        </>
      )}
    </>
  );
};

const DataQualityInsightsTabContent: FunctionComponent<DataQualityInsightsTabContentProps> = (props) => {
  return TabContentWithPortalDecorator(BaseDataQualityInsightsTabContent)({
    ...props,
    containerId: CONTAINER_ELEMENT_ID,
    tabName: DATA_QUALITY_INSIGHTS_TAB_NAME,
  });
};

export default DataQualityInsightsTabContent;
