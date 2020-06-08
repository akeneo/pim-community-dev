import React from 'react';
import { useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import {
  FamilyCondition,
  FamilyOperators,
} from '../../../../models/conditions';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { Operator } from '../../../../models/Operator';
import { FamilyCode } from '../../../../models';
import { FieldColumn, OperatorColumn, ValueColumn } from './style';
import { FamiliesSelector } from '../../../../components/Selectors/FamiliesSelector';
import { getFamiliesByIdentifiers } from '../../../../repositories/FamilyRepository';
import { LineErrors } from '../LineErrors';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';

type FamilyConditionLineProps = ConditionLineProps & {
  condition: FamilyCondition;
};

const FamilyConditionLine: React.FC<FamilyConditionLineProps> = ({
  lineNumber,
  currentCatalogLocale,
  condition,
}) => {
  const translate = useTranslate();
  const { watch } = useFormContext();
  const router = useBackboneRouter();
  const [unexistingFamilyCodes, setUnexistingFamilyCodes] = React.useState<
    FamilyCode[]
  >([]);

  useRegisterConst(`content.conditions[${lineNumber}].field`, condition.field);

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

  const [familyValidation, setValidate] = React.useState({
    validate: validateFamilyCodes,
  });
  React.useEffect(() => {
    setValidate({ validate: validateFamilyCodes });
  }, [JSON.stringify(unexistingFamilyCodes)]);

  return (
    <div className={'AknGrid-bodyCell'}>
      <FieldColumn
        className={'AknGrid-bodyCell--highlight'}
        title={translate('pimee_catalog_rule.form.edit.fields.family')}>
        {translate('pimee_catalog_rule.form.edit.fields.family')}
      </FieldColumn>
      <OperatorColumn>
        <OperatorSelector
          hiddenLabel={true}
          availableOperators={FamilyOperators}
          value={condition.operator}
          name={`content.conditions[${lineNumber}].operator`}
        />
      </OperatorColumn>
      {shouldDisplayValue() && (
        <ValueColumn>
          <FamiliesSelector
            hiddenLabel={true}
            currentCatalogLocale={currentCatalogLocale}
            value={condition.value}
            validation={familyValidation}
            name={`content.conditions[${lineNumber}].value`}
          />
        </ValueColumn>
      )}
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </div>
  );
};

export { FamilyConditionLine, FamilyConditionLineProps };
