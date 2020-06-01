import React from 'react';
import { useFormContext } from 'react-hook-form';
import {
  MultiOptionsAttributeCondition,
  MultiOptionsAttributeOperators,
} from '../../../../models/MultiOptionsAttributeCondition';
import { Operator } from '../../../../models/Operator';
import { ConditionLineProps } from './ConditionLineProps';
import { DefaultConditionLine } from './DefaultConditionLine';
import { MultiOptionsSelector } from "../../../../components/Selectors/MultiOptionsSelector";
import { useValueInitialization } from "../../hooks/useValueInitialization";

type MultiOptionsAttributeConditionLineProps = ConditionLineProps & {
  condition: MultiOptionsAttributeCondition;
};

const MultiOptionsAttributeConditionLine: React.FC<MultiOptionsAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  translate,
  locales,
  scopes,
  currentCatalogLocale,
  router,
}) => {
  const { setValue, watch } = useFormContext();

  useValueInitialization(
    `content.conditions[${lineNumber}]`,
    { value: condition.value },
    {},
    [condition]
  );

  const setValueFormValue = (value: string[] | null) =>
    setValue(`content.conditions[${lineNumber}].value`, value);

  const getValueFormValue: () => string[] = () =>
    watch(`content.conditions[${lineNumber}].value`);

  const shouldDisplayValue: (operator: Operator) => boolean = operator =>
    !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      operator
    );

  const onValueChange = (value: any) => {
    setValueFormValue(value);
  }

  return (
    <DefaultConditionLine
      condition={condition}
      lineNumber={lineNumber}
      translate={translate}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      shouldDisplayValue={shouldDisplayValue}
      availableOperators={MultiOptionsAttributeOperators}
      setValueFormValue={setValueFormValue}
    >
      <MultiOptionsSelector
        value={getValueFormValue() || []}
        onValueChange={onValueChange}
        id={`edit-rules-input-${lineNumber}-value`}
        currentCatalogLocale={'en_US'}
        router={router}
        collectionId={condition.attribute.meta.id}
        label={translate('pim_common.code')}
        hiddenLabel={true}
      />
    </DefaultConditionLine>
  );
};

export { MultiOptionsAttributeConditionLine, MultiOptionsAttributeConditionLineProps };
