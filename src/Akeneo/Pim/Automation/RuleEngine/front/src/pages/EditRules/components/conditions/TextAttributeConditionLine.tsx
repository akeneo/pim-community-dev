import React from 'react';
import { Controller } from 'react-hook-form';
import {
  TextAttributeCondition,
  TextAttributeOperators,
} from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { InputText } from '../../../../components/Inputs';
import { AttributeConditionLine } from './AttributeConditionLine';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { Attribute } from '../../../../models/Attribute';
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';

import { useControlledFormInputCondition } from '../../hooks';
import { Operator } from '../../../../models/Operator';

type TextAttributeConditionLineProps = ConditionLineProps & {
  condition: TextAttributeCondition;
};

const TextAttributeConditionLine: React.FC<TextAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const { valueFormName, getValueFormValue } = useControlledFormInputCondition<
    string[]
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
      availableOperators={TextAttributeOperators}
      currentCatalogLocale={currentCatalogLocale}
      defaultOperator={Operator.NOT_EQUAL}
      field={condition.field}
      lineNumber={lineNumber}
      locales={locales}
      scopes={scopes}>
      <Controller
        as={InputText}
        data-testid={`edit-rules-input-${lineNumber}-value`}
        name={valueFormName}
        label={translate('pimee_catalog_rule.rule.value')}
        hiddenLabel
        defaultValue={getValueFormValue()}
      />
    </AttributeConditionLine>
  );
};

export { TextAttributeConditionLine, TextAttributeConditionLineProps };
