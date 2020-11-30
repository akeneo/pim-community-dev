import React, {FC} from 'react';
import styled from 'styled-components';
import {Link} from 'akeneo-design-system';

const translate = require('oro/translator');

type RecommendationType = 'error' | 'success' | 'in_progress' | 'not_applicable' | 'to_improve';

type FollowRecommendationHandler = () => void;

type Props = {
  type: RecommendationType;
  follow?: FollowRecommendationHandler;
};

const LinkableMessage = styled(Link)`
  text-decoration: none;
`;

const Recommendation: FC<Props> = ({children, type, follow}) => {
  if (type === 'error') {
    return (
      <span className="CriterionErrorMessage" onClick={follow}>
        {children || translate(`akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error`)}
      </span>
    );
  }

  if (type === 'in_progress') {
    return (
      <span className="CriterionInProgressMessage" onClick={follow}>
        {children || translate(`akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress`)}
      </span>
    );
  }

  if (type === 'not_applicable') {
    return (
      <span className="NotApplicableAttribute" onClick={follow}>
        {children || (
          <>
            <span>
              {translate('akeneo_data_quality_insights.product_evaluation.messages.not_applicable.message')}&nbsp;
            </span>
            <LinkableMessage
              href={
                'https://help.akeneo.com/pim/serenity/articles/understand-data-quality.html#in-your-data-quality-insights-panel-product-edit-form'
              }
              target={'_blank'}
            >
              {translate('akeneo_data_quality_insights.product_evaluation.messages.not_applicable.help_center_link')}
            </LinkableMessage>
          </>
        )}
      </span>
    );
  }

  if (type === 'success') {
    return (
      <span className="CriterionSuccessMessage" onClick={follow}>
        {children || translate(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
      </span>
    );
  }

  return <>{children}</>;
};

export {Recommendation, RecommendationType, FollowRecommendationHandler};
