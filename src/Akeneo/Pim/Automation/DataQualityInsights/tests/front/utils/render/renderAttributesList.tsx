import React, {FC} from 'react';
import Evaluation from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {renderWithProductEditFormContextHelper} from './renderWithProductEditFormContextHelper';
import AttributeWithRecommendation from '@akeneo-pim-community/data-quality-insights/src/domain/AttributeWithRecommendation.interface';
import AttributesList from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Recommendation/AttributesList';

const renderAttributesList = (
  criterion: string,
  axis: string,
  attributes: AttributeWithRecommendation[],
  evaluation: Evaluation,
  appState = {}
) => {
  const Component: FC = () => (
    <AxesContextProvider axes={[axis]}>
      <AttributesList criterionCode={criterion} attributes={attributes} axis={axis} evaluation={evaluation} />
    </AxesContextProvider>
  );

  return renderWithProductEditFormContextHelper(<Component />, appState);
};

export {renderAttributesList};
