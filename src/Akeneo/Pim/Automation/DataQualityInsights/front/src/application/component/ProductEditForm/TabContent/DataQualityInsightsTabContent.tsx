import React, {Children, FC, FunctionComponent, ReactElement} from 'react';
import {get as _get} from 'lodash';
import AxisEvaluation from './DataQualityInsights/AxisEvaluation';
import {
  useCatalogContext,
  useFetchProductDataQualityEvaluation,
  useProductEvaluatedAttributeGroups,
} from '../../../../infrastructure/hooks';
import {CriterionEvaluationResult, Evaluation, Product, ProductEvaluation} from '../../../../domain';
import TabContentWithPortalDecorator from '../../TabContentWithPortalDecorator';
import {PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME, PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME} from '../../../constant';
import ProductEvaluationFetcher from '../../../../infrastructure/fetcher/ProductEditForm/ProductEvaluationFetcher';
import usePageContext from '../../../../infrastructure/hooks/ProductEditForm/usePageContext';
import {AttributeGroupsHelper} from './DataQualityInsights/AttributesGroupsHelper';
import {NoAttributeGroups} from './DataQualityInsights/NoAttributeGroups';
import {convertEvaluationToLegacyFormat} from '../../../helper';

export const CONTAINER_ELEMENT_ID = 'data-quality-insights-product-tab-content';

export interface DataQualityInsightsTabContentProps {
  productEvaluationFetcher: ProductEvaluationFetcher;
  product: Product;
}

const isProductEvaluationPending = (evaluation: ProductEvaluation | undefined, channel: string, locale: string) => {
  if (!evaluation || Object.keys(evaluation).length === 0) {
    return true;
  }

  const axisInProgress: any = Object.keys(evaluation).filter((axisCode: string) => {
    const axisEvaluation: Evaluation = _get(evaluation, [axisCode, channel, locale]);

    return (
      axisEvaluation.criteria.filter((criterion: CriterionEvaluationResult) => criterion.rate.value !== null).length ===
      0
    );
  });

  return axisInProgress.length > 0;
};

const BaseDataQualityInsightsTabContent: FC<DataQualityInsightsTabContentProps> = ({
  children,
  productEvaluationFetcher,
}) => {
  const {locale, channel} = useCatalogContext();
  const newEvaluation = useFetchProductDataQualityEvaluation(productEvaluationFetcher);
  const {evaluatedGroups, allGroupsEvaluated} = useProductEvaluatedAttributeGroups();

  const axes: any = {};
  Children.forEach(children, child => {
    const element = child as ReactElement;
    if (element.type === AxisEvaluation) {
      const criteria = Children.map(element.props.children, criterion => {
        return criterion.props.code;
      });
      axes[element.props.axis] = criteria;
    }
  });

  const productEvaluation: ProductEvaluation | undefined =
    // @ts-ignore
    newEvaluation && convertEvaluationToLegacyFormat(axes, newEvaluation);

  const hasEvaluation = channel && locale && !isProductEvaluationPending(productEvaluation, channel, locale);
  if (locale && channel && evaluatedGroups !== null && Object.keys(evaluatedGroups).length === 0 && !hasEvaluation) {
    return <NoAttributeGroups />;
  }

  return (
    <>
      {locale && channel && productEvaluation && (
        <>
          {hasEvaluation && (
            <AttributeGroupsHelper
              evaluatedAttributeGroups={evaluatedGroups}
              allGroupsEvaluated={allGroupsEvaluated}
              locale={locale}
            />
          )}
          {Children.map(children, child => {
            const element = child as ReactElement;
            if (element.type === AxisEvaluation) {
              const axisEvaluation: Evaluation = _get(productEvaluation, [element.props.axis, channel, locale], {
                rate: {
                  value: null,
                  rank: null,
                },
                criteria: [],
              });

              return React.cloneElement(element, {
                evaluation: axisEvaluation,
              });
            }
            return child;
          })}
        </>
      )}
    </>
  );
};

const DataQualityInsightsTabContent: FunctionComponent<DataQualityInsightsTabContentProps> = props => {
  const {product} = props;
  const tabName =
    product.meta.model_type === 'product'
      ? PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME
      : PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME;

  return TabContentWithPortalDecorator(
    BaseDataQualityInsightsTabContent,
    usePageContext
  )({
    ...props,
    containerId: CONTAINER_ELEMENT_ID,
    tabName: tabName,
  });
};

export default DataQualityInsightsTabContent;
