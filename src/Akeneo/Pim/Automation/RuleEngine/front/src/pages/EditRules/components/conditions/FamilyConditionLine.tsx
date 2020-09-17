import React from 'react';
import { Controller } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { FamilyOperators } from '../../../../models/conditions';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { Operator } from '../../../../models/Operator';
import { FamilyCode } from '../../../../models';
import {
  ConditionLineErrorsContainer,
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  OperatorColumn,
  ValueColumn,
} from './style';
import { FamiliesSelector } from '../../../../components/Selectors/FamiliesSelector';
import { getFamiliesByIdentifiers } from '../../../../repositories/FamilyRepository';
import { LineErrors } from '../LineErrors';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';

import { useControlledFormInputCondition } from '../../hooks';

const INIT_OPERATOR = Operator.IN_LIST;

const FamilyConditionLine: React.FC<ConditionLineProps> = ({
  lineNumber,
  currentCatalogLocale,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();

  const [unexistingFamilyCodes, setUnexistingFamilyCodes] = React.useState<
    FamilyCode[]
  >([]);

  const {
    fieldFormName,
    operatorFormName,
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  React.useEffect(() => {
    // This method stores the unexisting families at the loading of the line.
    // As there is no way to add unexisting families, the only solution for the user to validate is
    // to remove manually unexisting families.
    if (!getValueFormValue() || getValueFormValue().length === 0) {
      setUnexistingFamilyCodes([]);
    } else {
      getFamiliesByIdentifiers(getValueFormValue(), router).then(families => {
        const unexistingFamilies: FamilyCode[] = [];
        getValueFormValue().forEach(familyCode => {
          if (!families[familyCode]) {
            unexistingFamilies.push(familyCode);
          }
        });
        setUnexistingFamilyCodes(unexistingFamilies);
      });
    }
  }, []);

  const shouldDisplayValue: () => boolean = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getOperatorFormValue()
    );
  };

  const validateFamilyCodes = (familyCodes: FamilyCode[]) => {
    if (Array.isArray(familyCodes) && familyCodes.length === 0) {
      return translate('pimee_catalog_rule.exceptions.required');
    }
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
  return (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <ConditionLineFormContainer>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue='family'
        />
        <FieldColumn
          className={'AknGrid-bodyCell--highlight'}
          title={translate('pimee_catalog_rule.form.edit.fields.family')}>
          {translate('pimee_catalog_rule.form.edit.fields.family')}
        </FieldColumn>
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            availableOperators={FamilyOperators}
            data-testid={`edit-rules-input-${lineNumber}-operator`}
            hiddenLabel
            name={operatorFormName}
            defaultValue={getOperatorFormValue() ?? INIT_OPERATOR}
            value={getOperatorFormValue()}
          />
        </OperatorColumn>
        {shouldDisplayValue() && (
          <ValueColumn
            className={
              isFormFieldInError('value') ? 'select2-container-error' : ''
            }>
            <Controller
              as={FamiliesSelector}
              currentCatalogLocale={currentCatalogLocale}
              data-testid={`edit-rules-input-${lineNumber}-value`}
              defaultValue={getValueFormValue()}
              hiddenLabel
              name={valueFormName}
              rules={{
                validate: validateFamilyCodes,
                required: translate('pimee_catalog_rule.exceptions.required'),
              }}
              value={getValueFormValue()}
            />
          </ValueColumn>
        )}
      </ConditionLineFormContainer>
      <ConditionLineErrorsContainer>
        <LineErrors lineNumber={lineNumber} type='conditions' />
      </ConditionLineErrorsContainer>
    </ConditionLineFormAndErrorsContainer>
  );
};

export { FamilyConditionLine };
