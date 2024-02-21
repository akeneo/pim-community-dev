import React from 'react';
import {AttributeCode} from '../../../models';
import {useGetAttributeLabel} from '../../../hooks';

type Props = {attributeCode?: AttributeCode};

const AttributePropertyLine: React.FC<Props> = ({attributeCode}) => {
  const label = useGetAttributeLabel(attributeCode);

  return <>{label}</>;
};

export {AttributePropertyLine};
