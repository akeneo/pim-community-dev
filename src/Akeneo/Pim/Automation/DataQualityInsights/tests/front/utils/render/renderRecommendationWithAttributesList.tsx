import React, {FC} from 'react';
import {Product} from '@akeneo-pim-community/data-quality-insights/src/domain';
import Evaluation from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {RecommendationWithAttributesList} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights';
import {renderWithAppContextHelper} from './renderWithAppContextHelper';

const renderRecommendationWithAttributesList = (
  product: Product,
  criterion: string,
  axis: string,
  attributes: string[],
  evaluation: Evaluation,
  appState = {}
) => {
  const Component: FC = () => (
    <AxesContextProvider axes={[axis]}>
      <RecommendationWithAttributesList
        product={product}
        criterion={criterion}
        attributes={attributes}
        axis={axis}
        evaluation={evaluation}
      />
    </AxesContextProvider>
  );

  return renderWithAppContextHelper(<Component />, appState);
};

export {renderRecommendationWithAttributesList};
