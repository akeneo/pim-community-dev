import React from 'react';
import { useFormContext } from 'react-hook-form';
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
  const { register } = useFormContext();
  const [attribute, setAttribute] = React.useState<Attribute | null>();
  React.useEffect(() => {
    getAttributeByIdentifier(condition.field, router).then(attribute =>
      setAttribute(attribute)
    );
  });

  return (
    <AttributeConditionLine
      condition={condition}
      lineNumber={lineNumber}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      availableOperators={NumberAttributeOperators}
      attribute={attribute}>
      <InputNumber
        data-testid={`edit-rules-input-${lineNumber}-value`}
        name={`content.conditions[${lineNumber}].value`}
        label={translate('pimee_catalog_rule.rule.value')}
        ref={register()}
        hiddenLabel={true}
        defaultValue={condition.value}
        step={attribute?.decimals_allowed ? 'any' : 1}
      />
    </AttributeConditionLine>
  );
};

export { NumberAttributeConditionLine, NumberAttributeConditionLineProps };
