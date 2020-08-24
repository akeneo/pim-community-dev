import React from 'react';
import { Locale } from '../../../../../models';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import { Operation } from '../../../../../models/actions/Calculate/Operation';
import { useControlledFormInputAction } from '../../../hooks';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { useFormContext } from 'react-hook-form';
import styled from 'styled-components';
import {
  FieldOperand,
  Operand,
} from '../../../../../models/actions/Calculate/Operand';
import { AttributePropertiesSelector } from "../attribute/AttributePropertiesSelector";

type SourceOrOperation = Operand | Operation;

const DeleteButton = styled.button`
  border: none;
  background: none;
  cursor: pointer;
`;

type OperationLineProps = {
  baseFormName: string;
  sourceOrOperation: SourceOrOperation;
  locales: Locale[];
  scopes: IndexedScopes;
  lineNumber: number;
  operationLineNumber: number;
  removeOperation: (operationLineNumber: number) => () => void;
  isValue: boolean;
};

const ConcatenateOperationLine: React.FC<OperationLineProps> = ({
  baseFormName,
  sourceOrOperation,
  locales,
  scopes,
  lineNumber,
  operationLineNumber,
  removeOperation,
}) => {
  const translate = useTranslate();
  const { setValue, watch } = useFormContext();
  const { formName } = useControlledFormInputAction<
    string | null
  >(lineNumber);
  const fieldOperand = sourceOrOperation as FieldOperand;

  return (
    <li
      className={`AknRuleOperation-line`}
      data-line-number={operationLineNumber}
      data-testid={`edit-rules-action-operation-list-${operationLineNumber}-item`}>
      <div className={'AknRuleOperation-details'}>
        <div className={'AknRuleOperation-detailsContainer'}>
          <span
            className={`AknRuleOperation-moveIcon`}
            role={'operation-item-move-handle'}
          />
          <AttributePropertiesSelector
            operationLineNumber={operationLineNumber}
            attributeCode={fieldOperand.field}
            fieldFormName={formName(`${baseFormName}.field`)}
            localeFormName={formName(`${baseFormName}.locale`)}
            scopeFormName={formName(`${baseFormName}.scope`)}
            currencyFormName={formName(`${baseFormName}.currency`)}
            defaultLocale={watch(formName(`${baseFormName}.locale`))}
            defaultScope={watch(formName(`${baseFormName}.scope`))}
            defaultCurrency={watch(formName(`${baseFormName}.currency`))}
            onCurrencyChange={currencyCode => {
              setValue(formName(`${baseFormName}.currency`), currencyCode);
            }}
            onScopeChange={scopeCode => {
              setValue(formName(`${baseFormName}.scope`), scopeCode);
            }}
            onLocaleChange={localeCode => {
              setValue(formName(`${baseFormName}.locale`), localeCode);
            }}
            locales={locales}
            scopes={scopes}
          />
        </div>
      </div>
      <div className={'AknRuleOperation-remove'}>
        <DeleteButton
          type={'button'}
          onClick={removeOperation(operationLineNumber)}
          data-testid={`edit-rules-action-operation-list-${operationLineNumber}-remove-button`}>
          <img
            alt={translate('pimee_catalog_rule.form.edit.conditions.delete')}
            src='/bundles/akeneopimruleengine/assets/icons/icon-delete-grey100.svg'
          />
        </DeleteButton>
      </div>
    </li>
  );
};

export { ConcatenateOperationLine };
