import React, {FC, ReactElement} from 'react';
import {CriterionEvaluationResult} from '@akeneo-pim-community/data-quality-insights/src';
import Evaluation from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import Criterion, {
  AllowFollowingCriterionRecommendation,
  FollowCriterionRecommendationHandler,
} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion';
import {renderWithAppContextHelper} from './renderWithAppContextHelper';

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

  return renderWithAppContextHelper(<Component />, appState);
};

export {renderCriterion};
