import React, {FC} from 'react';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {renderWithProductEditFormContextHelper} from './renderWithProductEditFormContextHelper';
import {TooManyAttributesLink} from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Recommendation/TooManyAttributesLink';

const renderTooManyAttributesLink = (axis: string, attributes: string[], numOfAttributes: number, appState = {}) => {
  const Component: FC = () => (
    <AxesContextProvider axes={[axis]}>
      <TooManyAttributesLink axis={axis} attributes={attributes} numOfAttributes={numOfAttributes} />
    </AxesContextProvider>
  );

  return renderWithProductEditFormContextHelper(<Component />, appState);
};

export {renderTooManyAttributesLink};
