import React, {FC, ReactElement} from 'react';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {renderWithAppContextHelper} from './renderWithAppContextHelper';
import {Product} from '@akeneo-pim-community/data-quality-insights/src/domain';
import Attribute from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Attribute';

const renderAttribute = (
  code: string,
  label: string,
  separator: ReactElement | null,
  axis: string,
  product: Product,
  appState = {}
) => {
  const Component: FC = () => (
    <AxesContextProvider axes={[axis]}>
      <Attribute attributeCode={code} label={label} separator={separator} product={product} />
    </AxesContextProvider>
  );

  return renderWithAppContextHelper(<Component />, appState);
};


export {renderAttribute};
