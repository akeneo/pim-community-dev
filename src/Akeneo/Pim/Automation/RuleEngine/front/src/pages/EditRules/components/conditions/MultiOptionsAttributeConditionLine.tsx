import React from 'react';
import {
  MultiOptionsAttributeCondition,
  MultiOptionsAttributeOperators,
} from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { AttributeConditionLine } from './AttributeConditionLine';
import { MultiOptionsSelector } from '../../../../components/Selectors/MultiOptionsSelector';
import {
  AttributeOptionCode,
  getAttributeOptionsByIdentifiers,
} from '../../../../fetch/AttributeOptionFetcher';
import { useBackboneRouter, useTranslate } from "../../../../dependenciesTools/hooks";

type MultiOptionsAttributeConditionLineProps = ConditionLineProps & {
  condition: MultiOptionsAttributeCondition;
};

const MultiOptionsAttributeConditionLine: React.FC<MultiOptionsAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const [unexistingOptionCodes, setUnexistingOptionCodes] = React.useState<
    AttributeOptionCode[]
  >([]);

  React.useEffect(() => {
    // This method stores the unexisting option codes at the loading of the line.
    // As there is no way to add unexisting options, the only solution for the user to validate is
    // to remove manually unexisting options.
    if (!condition.value || condition.value.length === 0) {
      setUnexistingOptionCodes([]);
    } else {
      getAttributeOptionsByIdentifiers(
        condition.value,
        currentCatalogLocale,
        condition.attribute.meta.id,
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
  }, []);

  const validateOptionCodes = React.useCallback((optionCodes: AttributeOptionCode[]) => {
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
  }, [unexistingOptionCodes]);

  return (
    <AttributeConditionLine
      condition={condition}
      lineNumber={lineNumber}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      availableOperators={MultiOptionsAttributeOperators}
      >
      <MultiOptionsSelector
        value={condition.value || []}
        currentCatalogLocale={currentCatalogLocale}
        attributeId={condition.attribute.meta.id}
        label={translate('pimee_catalog_rule.rule.value')}
        hiddenLabel={true}
        name={`content.conditions[${lineNumber}].value`}
        validation={validateOptionCodes}
      />
    </AttributeConditionLine>
  );
};

export {
  MultiOptionsAttributeConditionLine,
  MultiOptionsAttributeConditionLineProps,
};
