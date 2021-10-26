import React from 'react';
import {AttributeCode} from "../models";

type AttributeSelectorProps = {
  label: string;
  readOnly: boolean;
  value: AttributeCode | null;
  onChange: (attributeCode: AttributeCode | null) => void;
};

const AttributeSelector: React.FC<AttributeSelectorProps> = ({
  label,
  readOnly,
  value,
  onChange
}) => {
  return <div>
    {label}
  </div>
}

export {AttributeSelector}
