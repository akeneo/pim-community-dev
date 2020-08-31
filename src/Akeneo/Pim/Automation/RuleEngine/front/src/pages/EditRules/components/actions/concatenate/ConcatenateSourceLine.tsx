import React from 'react';
import { Locale } from '../../../../../models';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import { useControlledFormInputAction } from '../../../hooks';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import styled from 'styled-components';
import { AttributePropertiesSelector } from '../attribute/AttributePropertiesSelector';
import { ConcatenateSource } from '../../../../../models/actions';

const DeleteButton = styled.button`
  border: none;
  background: none;
  cursor: pointer;
`;

type OperationLineProps = {
  baseFormName: string;
  source: ConcatenateSource;
  locales: Locale[];
  scopes: IndexedScopes;
  lineNumber: number;
  operationLineNumber: number;
  removeOperation: (operationLineNumber: number) => () => void;
  isValue: boolean;
};

const ConcatenateSourceLine: React.FC<OperationLineProps> = ({
  baseFormName,
  source,
  locales,
  scopes,
  lineNumber,
  operationLineNumber,
  removeOperation,
}) => {
  const translate = useTranslate();
  const { formName } = useControlledFormInputAction<string | null>(lineNumber);

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
            baseFormName={formName(baseFormName)}
            operationLineNumber={operationLineNumber}
            attributeCode={source.field}
            locales={locales}
            scopes={scopes}
            isCurrencyRequired={false}
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

export { ConcatenateSourceLine };
