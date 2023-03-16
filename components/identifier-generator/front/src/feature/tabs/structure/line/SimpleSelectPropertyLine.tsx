import React from 'react';
import {SimpleSelectProperty} from '../../../models';
import {useGetAttributeLabel} from '../../../hooks';

type Props = {property: SimpleSelectProperty};

const SimpleSelectPropertyLine: React.FC<Props> = ({property}) => {
  const label = useGetAttributeLabel(property.attributeCode);

  return <>{label}</>;
};

export {SimpleSelectPropertyLine};
