import React from 'react';
import { useFormContext } from 'react-hook-form';
import {
  MultiOptionsAttributeCondition,
  MultiOptionsAttributeOperators,
} from '../../../../models/conditions';
import { ConditionLineProps } from './ConditionLineProps';
import { AttributeConditionLine } from './AttributeConditionLine';
import { MultiOptionsSelector } from '../../../../components/Selectors/MultiOptionsSelector';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import {
  AttributeOptionCode,
  getAttributeOptionsByIdentifiers,
} from '../../../../fetch/AttributeOptionFetcher';

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

  const getValueFormValue: () => AttributeOptionCode[] = () =>
    watch(`content.conditions[${lineNumber}].value`);

  const validateOptionCodes = (optionCodes: AttributeOptionCode[]) => {
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

  useValueInitialization(
    `content.conditions[${lineNumber}]`,
    { value: condition.value },
    {
      value: {
        validate: validateOptionCodes,
      },
    },
    [condition, unexistingOptionCodes]
  );

  const setValueFormValue = (value: AttributeOptionCode[] | null) =>
    setValue(`content.conditions[${lineNumber}].value`, value);

  const onValueChange = (value: any) => {
    setValueFormValue(value);
  };

  return (
    <AttributeConditionLine
      condition={condition}
      lineNumber={lineNumber}
      translate={translate}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      availableOperators={MultiOptionsAttributeOperators}
      setValueFormValue={setValueFormValue}>
      <MultiOptionsSelector
        value={getValueFormValue() || []}
        onValueChange={onValueChange}
        id={`edit-rules-input-${lineNumber}-value`}
        currentCatalogLocale={currentCatalogLocale}
        router={router}
        attributeId={condition.attribute.meta.id}
        label={translate('pimee_catalog_rule.rule.value')}
        hiddenLabel={true}
      />
    </AttributeConditionLine>
  );
};

export {
  MultiOptionsAttributeConditionLine,
  MultiOptionsAttributeConditionLineProps,
};
