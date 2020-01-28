import React, {Fragment, FunctionComponent} from 'react';
import {get as _get} from 'lodash';
import AxisEvaluation from "./DataQualityInsights/AxisEvaluation";
import {useCatalogContext, useFetchProductDataQualityEvaluation} from "../../../../infrastructure/hooks";
import {Evaluation} from "../../../../domain";

interface DataQualityInsightsTabContentProps {
}

const DataQualityInsightsTabContent: FunctionComponent<DataQualityInsightsTabContentProps> = () => {
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

export default DataQualityInsightsTabContent;
