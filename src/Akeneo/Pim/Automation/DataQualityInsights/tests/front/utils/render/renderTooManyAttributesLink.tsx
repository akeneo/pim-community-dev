import React, {FC} from 'react';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {renderWithAppContextHelper} from './renderWithAppContextHelper';
import {Product} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {TooManyAttributesLink} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/TooManyAttributesLink';

const renderTooManyAttributesLink = (
  axis: string,
  attributes: string[],
  numOfAttributes: number,
  product: Product,
  appState = {}
) => {
  const Component: FC = () => (
    <AxesContextProvider axes={[axis]}>
      <TooManyAttributesLink axis={axis} attributes={attributes} numOfAttributes={numOfAttributes} product={product} />
    </AxesContextProvider>
  );

  return renderWithAppContextHelper(<Component />, appState);
};


export {renderTooManyAttributesLink};
