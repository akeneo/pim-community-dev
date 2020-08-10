import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import {
  SimpleMultiOptionsAttributeCondition,
  SimpleMultiOptionsAttributeOperators,
} from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { AttributeConditionLine } from './AttributeConditionLine';
import { MultiOptionsSelector } from '../../../../components/Selectors/MultiOptionsSelector';
import {
  AttributeOptionCode,
  getAttributeOptionsByIdentifiers,
} from '../../../../fetch/AttributeOptionFetcher';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { Attribute } from '../../../../models';
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';
import { useControlledFormInputCondition } from '../../hooks';
import { Operator } from '../../../../models/Operator';

type MultiOptionsAttributeConditionLineProps = ConditionLineProps & {
  condition: SimpleMultiOptionsAttributeCondition;
};

const SimpleMultiOptionsAttributeConditionLine: React.FC<MultiOptionsAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const { valueFormName, getValueFormValue } = useControlledFormInputCondition<
    string[]
  >(lineNumber);
  const router = useBackboneRouter();
  const translate = useTranslate();
  const { errors } = useFormContext();

  const [unexistingOptionCodes, setUnexistingOptionCodes] = React.useState<
    AttributeOptionCode[]
  >([]);

  const [attribute, setAttribute] = React.useState<Attribute | null>();
  React.useEffect(() => {
    getAttributeByIdentifier(condition.field, router).then(attribute =>
      setAttribute(attribute)
    );
  }, []);

  const isElementInError = (element: string): boolean =>
    typeof errors?.content?.conditions?.[lineNumber]?.[element] === 'object';

  React.useEffect(() => {
    if (!attribute) {
      return;
    }
    // This method stores the unexisting option codes at the loading of the line.
    // As there is no way to add unexisting options, the only solution for the user to validate is
    // to remove manually unexisting options.
    if (!condition.value || condition.value.length === 0) {
      setUnexistingOptionCodes([]);
    } else {
      getAttributeOptionsByIdentifiers(
        condition.value,
        currentCatalogLocale,
        attribute.meta.id,
        router
      ).then(select2Options => {
        const unexistingOptionCodes: AttributeOptionCode[] = [];
        (condition.value as AttributeOptionCode[]).forEach(
          attributeOptionCode => {
            if (
              !select2Options.some(select2Option => {
                return select2Option.id === attributeOptionCode;
              })
            ) {
              unexistingOptionCodes.push(attributeOptionCode);
            }
          }
        );
        setUnexistingOptionCodes(unexistingOptionCodes);
      });
    }
  }, [attribute]);

  const validation = (optionCodes: AttributeOptionCode[]) => {
    if (optionCodes && unexistingOptionCodes.length) {
      const unknownOptionCodes: AttributeOptionCode[] = [];
      optionCodes.forEach(familyCode => {
        if (unexistingOptionCodes.includes(familyCode)) {
          unknownOptionCodes.push(familyCode);
        }
      });
      if (unknownOptionCodes.length) {
        return translate(
          'pimee_catalog_rule.exceptions.unknown_attribute_options',
          {
            attributeOptionCodes: unknownOptionCodes.join(', '),
          },
          unknownOptionCodes.length
        );
      }
    }

    return true;
  };
  const [validateOptionCodes, setValidateOptionCodes] = React.useState({
    validate: validation,
    required: translate('pimee_catalog_rule.exceptions.required'),
  });
  React.useEffect(() => {
    setValidateOptionCodes({
      validate: validation,
      required: translate('pimee_catalog_rule.exceptions.required'),
    });
  }, [JSON.stringify(unexistingOptionCodes)]);

  return (
    <AttributeConditionLine
      defaultOperator={Operator.IS_EMPTY}
      field={condition.field}
      lineNumber={lineNumber}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      availableOperators={SimpleMultiOptionsAttributeOperators}
      attribute={attribute}
      valueHasError={isElementInError('value')}>
      {attribute && (
        <Controller
          as={MultiOptionsSelector}
          data-testid={`edit-rules-input-${lineNumber}-value`}
          value={getValueFormValue() ?? []}
          attributeId={attribute.meta.id}
          label={translate('pimee_catalog_rule.rule.value')}
          hiddenLabel
          name={valueFormName}
          rules={validateOptionCodes}
        />
      )}
    </AttributeConditionLine>
  );
};

export {
  SimpleMultiOptionsAttributeConditionLine,
  MultiOptionsAttributeConditionLineProps,
};
