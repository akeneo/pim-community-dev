import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { Operator } from '../../../../models/Operator';
import {
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  OperatorColumn,
  ValueColumn,
} from './style';
import { LineErrors } from '../LineErrors';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { useControlledFormInputCondition } from '../../hooks';
import { GroupCode } from '../../../../models';
import { getGroupsByIdentifiers } from '../../../../repositories/GroupRepository';
import { GroupOperators } from '../../../../models/conditions';
import { GroupsSelector } from '../../../../components/Selectors/GroupsSelector';

const DEFAULT_OPERATOR = Operator.IN_LIST;

const GroupsConditionLine: React.FC<ConditionLineProps> = ({
  lineNumber,
  currentCatalogLocale,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const { errors } = useFormContext();
  const {
    fieldFormName,
    operatorFormName,
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
  } = useControlledFormInputCondition<string[]>(lineNumber);
  const [unexistingGroupCodes, setUnexistingGroupCodes] = React.useState<
    GroupCode[]
  >([]);

  React.useEffect(() => {
    const groupCodes: GroupCode[] = getValueFormValue();

    if (!groupCodes?.length) {
      setUnexistingGroupCodes([]);
      return;
    }

    getGroupsByIdentifiers(groupCodes, router).then(groups => {
      setUnexistingGroupCodes(
        groupCodes.filter((groupCode: GroupCode) => !groups[groupCode])
      );
    });
  }, []);

  const shouldDisplayValue: () => boolean = () => {
    return !([Operator.IS_EMPTY, Operator.IS_NOT_EMPTY] as Operator[]).includes(
      getOperatorFormValue()
    );
  };

  const validateGroupCodes = (groupCodes: GroupCode[]) => {
    if (!groupCodes || !groupCodes.length) {
      return translate('pimee_catalog_rule.exceptions.required');
    }

    const unknownGroupCodes: GroupCode[] = groupCodes.filter(groupCode =>
      unexistingGroupCodes.includes(groupCode)
    );
    if (unknownGroupCodes.length) {
      return translate(
        'pimee_catalog_rule.exceptions.unknown_groups',
        {
          groupCodes: unknownGroupCodes.join(', '),
        },
        unknownGroupCodes.length
      );
    }

    return true;
  };

  const isElementInError = (element: string): boolean =>
    typeof errors?.content?.conditions?.[lineNumber]?.[element] === 'object';

  return (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <ConditionLineFormContainer>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue='groups'
        />
        <FieldColumn
          className={'AknGrid-bodyCell--highlight'}
          title={translate('pimee_catalog_rule.form.edit.fields.groups')}>
          {translate('pimee_catalog_rule.form.edit.fields.groups')}
        </FieldColumn>
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            availableOperators={GroupOperators}
            data-testid={`edit-rules-input-${lineNumber}-operator`}
            hiddenLabel
            name={operatorFormName}
            defaultValue={getOperatorFormValue() ?? DEFAULT_OPERATOR}
            value={getOperatorFormValue()}
          />
        </OperatorColumn>
        {shouldDisplayValue() && (
          <ValueColumn
            className={
              isElementInError('value') ? 'select2-container-error' : ''
            }>
            <Controller
              as={GroupsSelector}
              currentCatalogLocale={currentCatalogLocale}
              id={`edit-rules-input-${lineNumber}-value`}
              data-testid={`edit-rules-input-${lineNumber}-value`}
              defaultValue={getValueFormValue()}
              hiddenLabel
              name={valueFormName}
              rules={{ validate: validateGroupCodes }}
              value={getValueFormValue()}
            />
          </ValueColumn>
        )}
      </ConditionLineFormContainer>
      <LineErrors lineNumber={lineNumber} type='conditions' />
    </ConditionLineFormAndErrorsContainer>
  );
};

export { GroupsConditionLine };
