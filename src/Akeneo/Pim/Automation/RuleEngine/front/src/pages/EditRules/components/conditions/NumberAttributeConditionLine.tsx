import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import {
  NumberAttributeCondition,
  NumberAttributeOperators,
} from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { InputNumber } from '../../../../components/Inputs';
import { AttributeConditionLine } from './AttributeConditionLine';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { Attribute } from '../../../../models/Attribute';
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';
import { Operator } from '../../../../models/Operator';
import { useControlledFormInputCondition } from '../../hooks';

type NumberAttributeConditionLineProps = ConditionLineProps & {
  condition: NumberAttributeCondition;
};

const NumberAttributeConditionLine: React.FC<NumberAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const { errors } = useFormContext();
  const { valueFormName, getValueFormValue } = useControlledFormInputCondition<
    string[]
  >(lineNumber);
  const [attribute, setAttribute] = React.useState<Attribute | null>();
  React.useEffect(() => {
    getAttributeByIdentifier(condition.field, router).then(attribute =>
      setAttribute(attribute)
    );
  }, []);

  const isElementInError = (element: string): boolean =>
    typeof errors?.content?.conditions?.[lineNumber]?.[element] === 'object';

  return (
    <AttributeConditionLine
      attribute={attribute}
      availableOperators={NumberAttributeOperators}
      currentCatalogLocale={currentCatalogLocale}
      defaultOperator={Operator.IS_EMPTY}
      field={condition.field}
      lineNumber={lineNumber}
      locales={locales}
      scopes={scopes}>
      <Controller
        as={InputNumber}
        data-testid={`edit-rules-input-${lineNumber}-value`}
        name={valueFormName}
        label={translate('pimee_catalog_rule.rule.value')}
        hiddenLabel={true}
        defaultValue={getValueFormValue()}
        step={attribute?.decimals_allowed ? 'any' : 1}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required'),
        }}
        hasError={isElementInError('value')}
      />
    </AttributeConditionLine>
  );
};

export { NumberAttributeConditionLine, NumberAttributeConditionLineProps };
