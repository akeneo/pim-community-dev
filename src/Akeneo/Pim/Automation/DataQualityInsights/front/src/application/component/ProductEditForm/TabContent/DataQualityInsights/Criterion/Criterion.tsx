import React, {
  Children,
  cloneElement,
  createElement,
  FC,
  isValidElement,
  ReactElement,
  ReactNode,
  useMemo,
} from 'react';
import {Product} from '../../../../../../domain';
import Evaluation, {
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CRITERION_NOT_APPLICABLE,
  CriterionEvaluationResult,
} from '../../../../../../domain/Evaluation.interface';
import {Recommendation, RecommendationType, RecommendationWithAttributesList} from '../Recommendation';
import {useCatalogContext, useProduct, useProductFamily} from '../../../../../../infrastructure/hooks';
import {criterionPlaceholder, evaluationPlaceholder, isSimpleProduct, isSuccess} from '../../../../../helper';
import {
  AllowFollowingCriterionRecommendation,
  allowFollowingCriterionRecommendation as defaultAllowFollowingCriterionRecommendation,
  FollowAttributeRecommendationHandler,
  FollowAttributesListRecommendationHandler,
  followCriterionRecommendation as defaultFollowCriterionRecommendation,
  FollowCriterionRecommendationHandler,
} from '../../../../../user-actions';
import {Title} from './Title';
import {Icon} from './Icon';

interface CriterionProps {
  code: string;
  criterionEvaluation?: CriterionEvaluationResult;
  axis?: string;
  evaluation?: Evaluation;
  isFollowingCriterionRecommendationAllowed?: AllowFollowingCriterionRecommendation;
  followCriterionRecommendation?: FollowCriterionRecommendationHandler;
  followAttributeRecommendation?: FollowAttributeRecommendationHandler;
  followAttributesListRecommendation?: FollowAttributesListRecommendationHandler;
}

const getRecommendation = (children: ReactNode | null, type: RecommendationType): ReactElement | undefined => {
  let recommendation = <Recommendation type={type} />;

  Children.forEach(children, child => {
    if (React.isValidElement(child) && child.type === Recommendation && child.props.type === type) {
      recommendation = cloneElement(child);
    }
  });

  return recommendation;
};

const getToImproveRecommendation = (
  children: ReactNode | null,
  criterion: string,
  attributes: string[],
  product: Product,
  axis: string,
  evaluation: Evaluation,
  followAttributeRecommendation: FollowAttributeRecommendationHandler | undefined,
  followAttributesListRecommendation: FollowAttributesListRecommendationHandler | undefined
): ReactElement | undefined => {
  let recommendation: ReactElement | null = null;

  Children.forEach(children, child => {
    if (React.isValidElement(child) && child.type === Recommendation && child.props.type === 'to_improve') {
      recommendation = cloneElement(child);
    }
  });

  if (recommendation !== null) {
    return recommendation;
  }

  return (
    <RecommendationWithAttributesList
      criterion={criterion}
      attributes={attributes}
      product={product}
      axis={axis}
      evaluation={evaluation}
      followAttributeRecommendation={followAttributeRecommendation}
      followAttributesListRecommendation={followAttributesListRecommendation}
    />
  );
};

const buildRecommendation = (
  children: ReactNode | null | undefined,
  criterionEvaluation: CriterionEvaluationResult,
  evaluation: Evaluation,
  product: Product,
  axis: string,
  followAttributeRecommendation: FollowAttributeRecommendationHandler | undefined,
  followAttributesListRecommendation: FollowAttributesListRecommendationHandler | undefined
): ReactElement => {
  const criterion = criterionEvaluation.code;
  const attributes = criterionEvaluation.improvable_attributes || ([] as string[]);

  if ([CRITERION_ERROR, CRITERION_IN_PROGRESS, CRITERION_NOT_APPLICABLE].includes(criterionEvaluation.status)) {
    return <>{getRecommendation(children, criterionEvaluation.status as RecommendationType)}</>;
  }

  if (isSuccess(criterionEvaluation.rate)) {
    return (
      <div className="CriterionSuccessContainer">
        {getRecommendation(children, 'success')}
        <span className="CriterionSuccessTick" />
      </div>
    );
  }

  return (
    <>
      {getToImproveRecommendation(
        children,
        criterion,
        attributes,
        product,
        axis,
        evaluation,
        followAttributeRecommendation,
        followAttributesListRecommendation
      )}
    </>
  );
};

const buildIcon = (children?: ReactNode): ReactElement | undefined => {
  let icon: ReactElement | undefined = undefined;

  Children.forEach(children, child => {
    if (isValidElement(child) && child.type === Icon && child.props.type) {
      try {
        icon = cloneElement(child, {}, createElement(child.props.type));
      } catch (error) {
        console.error(error);
      }
    }
  });

  return icon;
};

const Criterion: FC<CriterionProps> = ({
  children,
  code,
  criterionEvaluation = criterionPlaceholder,
  axis = '',
  evaluation = evaluationPlaceholder,
  followCriterionRecommendation = defaultFollowCriterionRecommendation,
  isFollowingCriterionRecommendationAllowed = defaultAllowFollowingCriterionRecommendation,
  followAttributeRecommendation,
  followAttributesListRecommendation,
}) => {
  const criterion = code;
  const product = useProduct();
  const family = useProductFamily();
  const {locale} = useCatalogContext();
  const isClickable = isFollowingCriterionRecommendationAllowed(criterionEvaluation, family, product);
  const handleFollowingCriterionRecommendation =
    !isClickable || followCriterionRecommendation === undefined
      ? undefined
      : () => {
          followCriterionRecommendation(criterionEvaluation, family, product, locale as string);
        };

  const recommendation = useMemo(() => {
    return buildRecommendation(
      children,
      criterionEvaluation,
      evaluation,
      product,
      axis,
      followAttributeRecommendation,
      followAttributesListRecommendation
    );
  }, [
    children,
    criterionEvaluation,
    evaluation,
    product,
    axis,
    followAttributeRecommendation,
    followAttributesListRecommendation,
  ]);

  const rowProps = {
    className: `AknVerticalList-item ${isClickable ? 'AknVerticalList-item--clickable' : ''}`,
    onClick: handleFollowingCriterionRecommendation,
  };

  const icon = buildIcon(children);

  return (
    <li data-testid={'dqiProductEvaluationCriterion'} {...rowProps}>
      <div className={`CriterionMessage ${!isSimpleProduct(product) ? 'CriterionMessage--Variant' : ''}`}>
        {icon}
        <Title criterion={criterion} />
        <div>{recommendation}</div>
      </div>
    </li>
  );
};

export {Criterion};
