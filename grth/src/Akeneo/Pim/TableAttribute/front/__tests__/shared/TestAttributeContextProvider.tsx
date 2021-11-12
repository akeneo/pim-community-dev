import React from 'react';
import {AttributeContext} from '../../src/contexts';
import {TableAttribute} from '../../src/models';

export const TestAttributeContextProvider: React.FC<{attribute: TableAttribute}> = ({attribute, children}) => {
  const [attributeState, setAttributeState] = React.useState<TableAttribute>(attribute);

  return (
    <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
      {children}
    </AttributeContext.Provider>
  );
};
