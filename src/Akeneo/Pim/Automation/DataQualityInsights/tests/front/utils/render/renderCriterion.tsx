import React, {FC, ReactElement} from 'react';
import {CriterionEvaluationResult} from '@akeneo-pim-community/data-quality-insights/src';
import Evaluation from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {renderWithProductEditFormContextHelper} from './renderWithProductEditFormContextHelper';
import {Criterion} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion';
import {
  AllowFollowingCriterionRecommendation,
  FollowCriterionRecommendationHandler,
} from '@akeneo-pim-community/data-quality-insights/src/application/user-actions';

const renderCriterion = (
  code: string,
  criterionEvaluation: CriterionEvaluationResult,
  axis: string,
  evaluation: Evaluation,
  customContent: ReactElement | undefined = undefined,
  handleFollowCriterion: FollowCriterionRecommendationHandler | undefined = undefined,
  checkFollowingActive: AllowFollowingCriterionRecommendation | undefined = undefined,
  appState = {}
) => {
  const Component: FC = () => (
    <AxesContextProvider axes={[axis]}>
      <Criterion
        code={code}
        criterionEvaluation={criterionEvaluation}
        axis={axis}
        evaluation={evaluation}
        followCriterionRecommendation={handleFollowCriterion}
        isFollowingCriterionRecommendationAllowed={checkFollowingActive}
      >
        {customContent}
      </Criterion>
    </AxesContextProvider>
  );

  return renderWithProductEditFormContextHelper(<Component />, appState);
};

export {renderCriterion};
