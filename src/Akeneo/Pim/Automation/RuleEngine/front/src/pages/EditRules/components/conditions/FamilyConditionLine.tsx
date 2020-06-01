import React from 'react';
import { useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import {
  FamilyCondition,
  FamilyOperators,
} from '../../../../models/FamilyCondition';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { Operator } from '../../../../models/Operator';
import { FamilyCode } from '../../../../models';
import { FieldColumn, OperatorColumn, ValueColumn } from './style';
import { FamiliesSelector } from '../../../../components/Selectors/FamiliesSelector';
import { getFamiliesByIdentifiers } from '../../../../repositories/FamilyRepository';
import { ConditionLineErrors } from './ConditionLineErrors';

type FamilyConditionLineProps = ConditionLineProps & {
  condition: FamilyCondition;
};

const FamilyConditionLine: React.FC<FamilyConditionLineProps> = ({
  router,
  lineNumber,
  translate,
  currentCatalogLocale,
  condition,
}) => {
  const { watch, setValue } = useFormContext();
  const [unexistingFamilyCodes, setUnexistingFamilyCodes] = React.useState<
    FamilyCode[]
  >([]);

  React.useEffect(() => {
    // This method stores the unexisting families at the loading of the line.
    // As there is no way to add unexisting families, the only solution for the user to validate is
    // to remove manually unexisting families.
    if (!condition.value || condition.value.length === 0) {
      setUnexistingFamilyCodes([]);
    } else {
      getFamiliesByIdentifiers(condition.value, router).then(families => {
        const unexistingFamilies: FamilyCode[] = [];
        condition.value.forEach(familyCode => {
          if (!families[familyCode]) {
            unexistingFamilies.push(familyCode);
          }
        });
        setUnexistingFamilyCodes(unexistingFamilies);
      });
    }
  }, []);

  const getOperatorFormValue: () => Operator = () =>
    watch(`content.conditions[${lineNumber}].operator`);
  const getValueFormValue: () => FamilyCode[] = () =>
    watch(`content.conditions[${lineNumber}].value`);

  const shouldDisplayValue: () => boolean = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getOperatorFormValue()
    );
  };

  const validateFamilyCodes = (familyCodes: FamilyCode[]) => {
    if (familyCodes && unexistingFamilyCodes.length) {
      const unknownFamilyCodes: FamilyCode[] = [];
      familyCodes.forEach(familyCode => {
        if (unexistingFamilyCodes.includes(familyCode)) {
          unknownFamilyCodes.push(familyCode);
        }
      });
      if (unknownFamilyCodes.length) {
        return translate(
          'pimee_catalog_rule.exceptions.unknown_families',
          {
            familyCodes: unknownFamilyCodes.join(', '),
          },
          unknownFamilyCodes.length
        );
      }
    }

    return true;
  };

  useValueInitialization(
    `content.conditions[${lineNumber}]`,
    {
      field: condition.field,
      operator: condition.operator,
      value: condition.value,
    },
    {
      value: {
        validate: validateFamilyCodes,
      },
    },
    [condition, unexistingFamilyCodes]
  );

  const setValueFormValue = (value: FamilyCode[] | null) => {
    setValue(`content.conditions[${lineNumber}].value`, value);
  };
  const setOperatorFormValue = (value: Operator) => {
    setValue(`content.conditions[${lineNumber}].operator`, value);
    if (!shouldDisplayValue()) {
      setValueFormValue(null);
    }
  };

  return (
    <div className={'AknGrid-bodyCell'}>
      <FieldColumn
        className={'AknGrid-bodyCell--highlight'}
        title={translate('pimee_catalog_rule.form.edit.fields.family')}>
        {translate('pimee_catalog_rule.form.edit.fields.family')}
      </FieldColumn>
      <OperatorColumn>
        <OperatorSelector
          id={`edit-rules-input-${lineNumber}-operator`}
          label='Operator'
          hiddenLabel={true}
          availableOperators={FamilyOperators}
          translate={translate}
          value={getOperatorFormValue()}
          onChange={setOperatorFormValue}
        />
      </OperatorColumn>
      {shouldDisplayValue() && (
        <ValueColumn>
          <FamiliesSelector
            router={router}
            id={`edit-rules-input-${lineNumber}-value`}
            label='Families'
            hiddenLabel={true}
            currentCatalogLocale={currentCatalogLocale}
            value={getValueFormValue()}
            onChange={setValueFormValue}
          />
        </ValueColumn>
      )}
      <ConditionLineErrors lineNumber={lineNumber} />
    </div>
  );
};

export { FamilyConditionLine, FamilyConditionLineProps };
