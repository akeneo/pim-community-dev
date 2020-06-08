import React from 'react';
import { useFormContext } from 'react-hook-form';
import {
  TextAttributeCondition,
  TextAttributeOperators,
} from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { InputText } from '../../../../components/Inputs';
import { AttributeConditionLine } from './AttributeConditionLine';
import { useBackboneRouter, useTranslate } from "../../../../dependenciesTools/hooks";
import { Attribute } from "../../../../models/Attribute";
import { getAttributeByIdentifier } from "../../../../repositories/AttributeRepository";

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
  const { register, getValues } = useFormContext();
  const [ attribute, setAttribute ] = React.useState<Attribute | null>();
  React.useEffect(() => {
    getAttributeByIdentifier(condition.field, router).then(attribute => setAttribute(attribute));
  });

  React.useEffect(() => {
    console.log('Condition', JSON.stringify(condition));
  }, [ JSON.stringify(condition) ]);

  React.useEffect(() => {
    console.log('GetValues', JSON.stringify(getValues()));
  }, [ JSON.stringify(getValues()) ]);

  //React.useEffect(() => {
    /* Theorically, adding a ref={register} the <Input> should be sufficient. As the AttributeConditionLine is
     * loaded before the input, it custom registers the form values and loose the condition.value. We have to register
     * it here to avoid loosing it. */
  /*  console.log('Register value', condition.value);
    register({ name: `content.conditions[${lineNumber}].value` });
    setValue(`content.conditions[${lineNumber}].value`, condition.value);
  }, []);
*/
/*  const setValuez = (val: any) => {
    console.log('setValue', val.target.value);
    setValue(`content.conditions[${lineNumber}].value`, val.target.value);
  }*/

  /*
  const getDef = () => {
    const x = ;
    const y = watch(`content.conditions[${lineNumber}].value`, condition.value);
    console.log('GetDef', x, y);
    return x;
  }*/

  return (
    <AttributeConditionLine
      condition={condition}
      lineNumber={lineNumber}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      availableOperators={TextAttributeOperators}
      attribute={attribute}
    >
      <InputText
        name={`content.conditions[${lineNumber}].value`}
        label={translate('pimee_catalog_rule.rule.value')}
        ref={register()}
        hiddenLabel={true}
        defaultValue={condition.value}
      />
    </AttributeConditionLine>
  );
};

export { TextAttributeConditionLine, TextAttributeConditionLineProps };
