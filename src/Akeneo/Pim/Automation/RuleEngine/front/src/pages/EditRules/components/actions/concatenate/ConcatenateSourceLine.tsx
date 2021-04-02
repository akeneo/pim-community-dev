import React from 'react';
import {Controller, useFormContext} from 'react-hook-form';
import {Locale} from '../../../../../models';
import {IndexedScopes} from '../../../../../repositories/ScopeRepository';
import {useControlledFormInputAction} from '../../../hooks';
import {useTranslate} from '../../../../../dependenciesTools/hooks';
import styled from 'styled-components';
import {AttributePropertiesSelector} from '../attribute/AttributePropertiesSelector';
import {ConcatenateSource} from '../../../../../models/actions';
import {InputText} from '../../../../../components/Inputs';

const DeleteButton = styled.button`
  border: none;
  background: none;
  cursor: pointer;
`;

type OperationLineProps = {
  baseFormName: string;
  source: ConcatenateSource;
  locales: Locale[];
  uiLocales: Locale[];
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
  uiLocales,
  scopes,
  lineNumber,
  operationLineNumber,
  removeOperation,
}) => {
  const translate = useTranslate();
  const {
    formName,
    getFormValue,
    isFormFieldInError,
  } = useControlledFormInputAction<string | null>(lineNumber);
  const {setValue, watch} = useFormContext();

  const getText = () =>
    getFormValue(formName(`${baseFormName}.text`)) ?? source.text;
  watch(formName(`${baseFormName}.text`));

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
          {source.field && (
            <AttributePropertiesSelector
              baseFormName={formName(baseFormName)}
              operationLineNumber={operationLineNumber}
              attributeCode={source.field}
              locales={locales}
              uiLocales={uiLocales}
              scopes={scopes}
              isCurrencyRequired={false}
              context={'concatenate'}
            />
          )}
          {null !== source.text && 'undefined' !== typeof source.text && (
            <>
              <Controller
                as={<span hidden />}
                name={formName(`${baseFormName}.text`)}
                defaultValue={getText()}
              />
              <label
                className={`AknInputSizer${
                  isFormFieldInError(
                    `full_operation_list[${operationLineNumber}].value`
                  )
                    ? 'AknInputSizer--error'
                    : ''
                }`}
                data-value={getText()}>
                <InputText
                  defaultValue={getText()}
                  className={'AknInputSizer-input'}
                  data-testid={`edit-rules-action-operation-list-${operationLineNumber}-text`}
                  hiddenLabel={true}
                  onChange={e => {
                    setValue(
                      formName(`${baseFormName}.text`),
                      e.target.value ? e.target.value : ''
                    );
                  }}
                  size={2}
                />
              </label>
            </>
          )}
          {'undefined' !== typeof source.new_line && (
            <>
              <Controller
                as={<span hidden />}
                name={formName(`${baseFormName}.new_line`)}
                defaultValue={null}
              />
              <span className={'AknRuleOperation-elementNewLine'}>
                {translate(
                  'pimee_catalog_rule.form.edit.actions.concatenate.line_break'
                )}
              </span>
            </>
          )}
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

export {ConcatenateSourceLine};
