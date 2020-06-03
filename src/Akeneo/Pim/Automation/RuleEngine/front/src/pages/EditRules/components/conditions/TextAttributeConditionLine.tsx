import React from 'react';
import { useFormContext } from 'react-hook-form';
import {
  TextAttributeCondition,
  TextAttributeOperators,
} from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { InputText } from '../../../../components/Inputs';
import { AttributeConditionLine } from './AttributeConditionLine';

type TextAttributeConditionLineProps = ConditionLineProps & {
  condition: TextAttributeCondition;
};

const TextAttributeConditionLine: React.FC<TextAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  translate,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const { register } = useFormContext();

  return (
    <AttributeConditionLine
      condition={condition}
      lineNumber={lineNumber}
      translate={translate}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      availableOperators={TextAttributeOperators}
    >
      <InputText
        name={`content.conditions[${lineNumber}].value`}
        label={translate('pimee_catalog_rule.rule.value')}
        ref={register}
        hiddenLabel={true}
      />
    </AttributeConditionLine>
  );
};

export { TextAttributeConditionLine, TextAttributeConditionLineProps };
