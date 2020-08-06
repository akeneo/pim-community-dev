import React from 'react';
import { ConditionLineProps } from './ConditionLineProps';
import { AttributeConditionLine } from './AttributeConditionLine';
import {
  useBackboneRouter,
  useUserCatalogLocale,
  useUserCatalogScope,
} from '../../../../dependenciesTools/hooks';
import { Attribute } from '../../../../models';
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';
import { Operator } from '../../../../models/Operator';
import {
  SimpleMultiReferenceEntitiesAttributeCondition,
  SimpleMultiReferenceEntitiesAttributeOperators,
} from '../../../../models/conditions/SimpleMultiReferenceEntitiesAttributeCondition';
import { ReferenceEntitySelector } from '../../../../dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector';

type SimpleMultiReferenceEntitiesAttributeConditionLineProps = ConditionLineProps & {
  condition: SimpleMultiReferenceEntitiesAttributeCondition;
};

const SimpleMultiReferenceEntitiesAttributeConditionLine: React.FC<SimpleMultiReferenceEntitiesAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const userCatalogLocale = useUserCatalogLocale();
  const userCatalogScope = useUserCatalogScope();

  const [attribute, setAttribute] = React.useState<Attribute | null>();
  React.useEffect(() => {
    getAttributeByIdentifier(condition.field, router).then(attribute =>
      setAttribute(attribute)
    );
  }, []);

  if (!attribute) {
    return <div>LOADING</div>;
  }

  return (
    <AttributeConditionLine
      defaultOperator={Operator.IS_EMPTY}
      field={condition.field}
      lineNumber={lineNumber}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      availableOperators={SimpleMultiReferenceEntitiesAttributeOperators}
      attribute={attribute}>
      {attribute && (
        <ReferenceEntitySelector
          value={condition.value || []}
          referenceEntityIdentifier={attribute.reference_data_name as string}
          locale={userCatalogLocale}
          channel={userCatalogScope}
          placeholder={'TODO CHANGE THIS'}
          onChange={(value: any) => {
            console.log(value);
          }}
          multiple={true}
        />
        /*<Controller
          as={MultiReferenceEntitiesSelector}
          data-testid={`edit-rules-input-${lineNumber}-value`}
          value={getValueFormValue() ?? []}
          attributeId={attribute.meta.id}
          label={translate('pimee_catalog_rule.rule.value')}
          hiddenLabel
          name={valueFormName}
          validation={validateOptionCodes}
        />*/
      )}
    </AttributeConditionLine>
  );
};

export {
  SimpleMultiReferenceEntitiesAttributeConditionLine,
  SimpleMultiReferenceEntitiesAttributeConditionLineProps,
};
