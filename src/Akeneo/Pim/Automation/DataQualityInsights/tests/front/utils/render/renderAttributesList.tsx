import React, {FC} from 'react';
import Evaluation from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {renderWithAppContextHelper} from './renderWithAppContextHelper';
import AttributesListWithVariations from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AttributesListWithVariations';
import {Product} from '@akeneo-pim-community/data-quality-insights/src/domain';
import AttributeWithRecommendation from '@akeneo-pim-community/data-quality-insights/src/domain/AttributeWithRecommendation.interface';

const renderAttributesListWithVariations = (
  product: Product,
  criterion: string,
  locale: string,
  axis: string,
  attributes: AttributeWithRecommendation[],
  evaluation: Evaluation,
  appState = {}
) => {
  const Component: FC = () => (
    <AxesContextProvider axes={[axis]}>
      <AttributesListWithVariations
        product={product}
        criterionCode={criterion}
        attributes={attributes}
        locale={locale}
        axis={axis}
        evaluation={evaluation}
      />
    </AxesContextProvider>
  );

  return renderWithAppContextHelper(<Component />, appState);
};

export {renderAttributesListWithVariations};
