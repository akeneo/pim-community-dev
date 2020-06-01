import React from 'react';
import { useFormContext } from 'react-hook-form';
import {
  TextAttributeCondition,
  TextAttributeOperators,
} from '../../../../models/conditions';
import { Operator } from '../../../../models/Operator';
import { ConditionLineProps } from './ConditionLineProps';
import { InputText } from '../../../../components/Inputs';
import { DefaultConditionLine } from './DefaultConditionLine';

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
  const { register, setValue } = useFormContext();

  const setValueFormValue = (value: string[] | null) =>
    setValue(`content.conditions[${lineNumber}].value`, value);

  const shouldDisplayValue: (operator: Operator) => boolean = operator =>
    !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      operator
    );

  return (
    <DefaultConditionLine
      condition={condition}
      lineNumber={lineNumber}
      translate={translate}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      shouldDisplayValue={shouldDisplayValue}
      availableOperators={TextAttributeOperators}
      setValueFormValue={setValueFormValue}>
      <InputText
        data-testid={`edit-rules-input-${lineNumber}-value`}
        name={`content.conditions[${lineNumber}].value`}
        label={translate('pim_common.code')}
        ref={register}
        hiddenLabel={true}
      />
    </DefaultConditionLine>
  );
};

export { TextAttributeConditionLine, TextAttributeConditionLineProps };
