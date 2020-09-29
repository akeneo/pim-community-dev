import React, {Fragment, FunctionComponent} from 'react';
import {get as _get} from 'lodash';
import AxisEvaluation from "./DataQualityInsights/AxisEvaluation";
import {useCatalogContext, useFetchProductDataQualityEvaluation} from "../../../../infrastructure/hooks";
import {Evaluation, Product} from '../../../../domain';
import TabContentWithPortalDecorator from "../../TabContentWithPortalDecorator";
import {PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME, PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME} from '../../../constant';
import ProductEvaluationFetcher from "../../../../infrastructure/fetcher/ProductEditForm/ProductEvaluationFetcher";
import usePageContext from "../../../../infrastructure/hooks/ProductEditForm/usePageContext";

export const CONTAINER_ELEMENT_ID = 'data-quality-insights-product-tab-content';

export interface DataQualityInsightsTabContentProps {
  productEvaluationFetcher: ProductEvaluationFetcher;
  product: Product;
}

const BaseDataQualityInsightsTabContent: FunctionComponent<DataQualityInsightsTabContentProps> = ({productEvaluationFetcher}: DataQualityInsightsTabContentProps) => {
  const {locale, channel} = useCatalogContext();
  const productEvaluation = useFetchProductDataQualityEvaluation(productEvaluationFetcher);

  return (
    <>
      {locale && channel && productEvaluation && (
        <>
        {Object.entries(productEvaluation).map(([code, axisEvaluationData]) => {
          const axisEvaluation: Evaluation = _get(axisEvaluationData, [channel, locale], {
            rate: {
              value: null,
              rank: null,
            },
            criteria: [],
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
  const {product} = props;
  const tabName = product.meta.model_type === "product" ? PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME : PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME;

  return TabContentWithPortalDecorator(BaseDataQualityInsightsTabContent, usePageContext)({
    ...props,
    containerId: CONTAINER_ELEMENT_ID,
    tabName: tabName,
  });
};

export default DataQualityInsightsTabContent;
