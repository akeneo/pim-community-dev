import React from 'react';
import { Controller } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { Operator } from '../../../../models/Operator';
import { FamilyVariantCode, FamilyVariantOperators } from '../../../../models';
import {
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  OperatorColumn,
  ValueColumn,
} from './style';
import { getFamilyVariantsByIdentifiers } from '../../../../repositories/FamilyVariantRepository';
import { LineErrors } from '../LineErrors';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { useControlledFormInputCondition } from '../../hooks';
import { FamilyVariantsSelector } from '../../../../components/Selectors/FamilyVariantsSelector';

const INIT_OPERATOR = Operator.IN_LIST;

const FamilyVariantConditionLine: React.FC<ConditionLineProps> = ({
  lineNumber,
  currentCatalogLocale,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();

  const {
    fieldFormName,
    operatorFormName,
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  const [
    unexistingFamilyVariantCodes,
    setUnexistingFamilyVariantCodes,
  ] = React.useState<FamilyVariantCode[]>([]);

  React.useEffect(() => {
    // This method stores the unexisting family variants at the loading of the line.
    // As there is no way to add unexisting family variants, the only solution for the user to validate is
    // to manually remove unexisting family variants.
    const selectedFamilyVariantCodes = getValueFormValue();

    if (
      !selectedFamilyVariantCodes ||
      selectedFamilyVariantCodes.length === 0
    ) {
      setUnexistingFamilyVariantCodes([]);
    } else {
      getFamilyVariantsByIdentifiers(
        selectedFamilyVariantCodes,
        router
      ).then(existingFamilyVariants =>
        setUnexistingFamilyVariantCodes(
          selectedFamilyVariantCodes.filter(
            familyVariantCode => !(familyVariantCode in existingFamilyVariants)
          )
        )
      );
    }
  }, []);

  const shouldDisplayValue: () => boolean = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getOperatorFormValue()
    );
  };

  const validateFamilyVariantCodes = (
    familyVariantCodes: FamilyVariantCode[]
  ) => {
    if (Array.isArray(familyVariantCodes) && familyVariantCodes.length === 0) {
      return translate('pimee_catalog_rule.exceptions.required');
    }
    if (familyVariantCodes && unexistingFamilyVariantCodes.length) {
      const unknownFamilyVariantCodes: FamilyVariantCode[] = familyVariantCodes.filter(
        familyVariantCode =>
          unexistingFamilyVariantCodes.includes(familyVariantCode)
      );
      if (unknownFamilyVariantCodes.length) {
        return translate(
          'pimee_catalog_rule.exceptions.unknown_family_variants',
          {
            familyVariantCodes: unknownFamilyVariantCodes.join(', '),
          },
          unknownFamilyVariantCodes.length
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
          defaultValue='family_variant'
        />
        <FieldColumn
          className={'AknGrid-bodyCell--highlight'}
          title={translate(
            'pimee_catalog_rule.form.edit.fields.family_variant'
          )}>
          {translate('pimee_catalog_rule.form.edit.fields.family_variant')}
        </FieldColumn>
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            availableOperators={FamilyVariantOperators}
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
              as={FamilyVariantsSelector}
              currentCatalogLocale={currentCatalogLocale}
              id={`edit-rules-input-${lineNumber}-value`}
              defaultValue={getValueFormValue()}
              hiddenLabel
              name={valueFormName}
              rules={{
                validate: validateFamilyVariantCodes,
                required: translate('pimee_catalog_rule.exceptions.required'),
              }}
              value={getValueFormValue()}
            />
          </ValueColumn>
        )}
      </ConditionLineFormContainer>
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </ConditionLineFormAndErrorsContainer>
  );
};

export { FamilyVariantConditionLine };
