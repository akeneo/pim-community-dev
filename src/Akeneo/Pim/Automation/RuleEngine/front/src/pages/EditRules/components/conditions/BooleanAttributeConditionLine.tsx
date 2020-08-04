import React from 'react';
import { Controller } from 'react-hook-form';
import {
  BooleanAttributeCondition,
  BooleanAttributeOperators,
} from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { AttributeConditionLine } from './AttributeConditionLine';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { Attribute } from '../../../../models';
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';
import { useControlledFormInputCondition } from '../../hooks';
import InputBoolean from '../../../../components/Inputs/InputBoolean';

type BooleanAttributeConditionLineProps = ConditionLineProps & {
  condition: BooleanAttributeCondition;
};

const BooleanAttributeConditionLine: React.FC<BooleanAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const { valueFormName, getValueFormValue } = useControlledFormInputCondition<
    boolean
  >(lineNumber);
  const [attribute, setAttribute] = React.useState<Attribute | null>();
  React.useEffect(() => {
    getAttributeByIdentifier(condition.field, router).then(attribute =>
      setAttribute(attribute)
    );
  }, []);

  return (
    <AttributeConditionLine
      attribute={attribute}
      availableOperators={BooleanAttributeOperators}
      currentCatalogLocale={currentCatalogLocale}
      defaultOperator={condition.operator}
      field={condition.field}
      lineNumber={lineNumber}
      locales={locales}
      scopes={scopes}>
      <Controller
        as={InputBoolean}
        id={`edit-rules-input-${lineNumber}-value`}
        name={valueFormName}
        label={translate('pimee_catalog_rule.rule.value')}
        hiddenLabel={true}
        defaultValue={condition.value}
        value={getValueFormValue()}
      />
    </AttributeConditionLine>
  );
};

export { BooleanAttributeConditionLine, BooleanAttributeConditionLineProps };
