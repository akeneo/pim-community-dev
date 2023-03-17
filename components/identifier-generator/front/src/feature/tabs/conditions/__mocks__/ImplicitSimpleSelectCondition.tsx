import React from 'react';
import {SimpleSelectProperty} from '../../../models';

type Props = {
  simpleSelectProperty: SimpleSelectProperty;
};

const ImplicitSimpleSelectCondition: React.FC<Props> = ({simpleSelectProperty}) => (
  <>
    <p>ImplicitSimpleSelectConditionMocked</p>
    <p>Implicit attribute code: {simpleSelectProperty.attributeCode}</p>
    {simpleSelectProperty.scope && (
      <p>
        <span>Implicit attribute scope:</span> <span>{simpleSelectProperty.scope}</span>
      </p>
    )}
    {simpleSelectProperty.locale && (
      <p>
        <span>Implicit attribute locale:</span> <span>{simpleSelectProperty.locale}</span>
      </p>
    )}
  </>
);

export {ImplicitSimpleSelectCondition};
