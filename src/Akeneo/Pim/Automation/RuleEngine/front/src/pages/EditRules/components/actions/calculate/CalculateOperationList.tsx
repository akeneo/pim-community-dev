// eslint-disable-next-line @typescript-eslint/no-var-requires
const Dragula = require('react-dragula');
import React from 'react';
import {useFieldArray, useFormContext} from 'react-hook-form';
import {Operation} from '../../../../../models/actions/Calculate/Operation';
import {useTranslate} from '../../../../../dependenciesTools/hooks';
import {AttributeCode, AttributeType, Locale} from '../../../../../models';
import {IndexedScopes} from '../../../../../repositories/ScopeRepository';
import {useControlledFormInputAction} from '../../../hooks';
import {CalculateOperationLine} from './CalculateOperationLine';
import {AddFieldButton} from '../../../../../components/Selectors/AddFieldButton';
import {BlueGhostButton} from '../../../../../components/Buttons';

type Props = {
  lineNumber: number;
  onChange?: (value: Operation[]) => void;
  locales: Locale[];
  uiLocales: Locale[];
  scopes: IndexedScopes;
};

const CalculateOperationList: React.FC<Props> = ({
  lineNumber,
  locales,
  uiLocales,
  scopes,
}) => {
  const translate = useTranslate();
  const {formName, isFormFieldInError} = useControlledFormInputAction<
    string | null
  >(lineNumber);

  const {fields, remove, move, append} = useFieldArray({
    name: formName('full_operation_list'),
  });
  const {watch} = useFormContext();

  const removeOperation = (lineToRemove: number) => () => {
    remove(lineToRemove);
  };

  const moveOperation = (
    currentOperationLineNumber: number,
    newOperationLineNumber: number
  ) => {
    if (currentOperationLineNumber === newOperationLineNumber) {
      return;
    }

    move(currentOperationLineNumber, newOperationLineNumber);
  };

  const handleAddValue = () => {
    if (fields.length > 0) {
      append({operator: null, value: ''});
    } else {
      append({value: ''});
    }
  };

  const handleAddAttribute = (attributeCode: AttributeCode) => {
    if (fields.length > 0) {
      append({operator: null, field: attributeCode});
    } else {
      append({field: attributeCode});
    }
  };

  const dragulaDecorator = React.useRef(null);
  React.useEffect(() => {
    if (dragulaDecorator.current) {
      const options = {};
      const drake = Dragula([dragulaDecorator.current], options);
      drake.on(
        'drop',
        (
          el: HTMLElement,
          _target: HTMLElement,
          _source: HTMLElement,
          sibling: HTMLElement
        ) => {
          const origin = Number(el.dataset.lineNumber);
          const fields: [] = watch(formName('full_operation_list')) ?? [];
          let target = Number(sibling?.dataset?.lineNumber ?? fields.length);
          if (target > origin) {
            target--;
          }
          moveOperation(origin, target);
        }
      );
    }
  }, [dragulaDecorator]);

  const isSourceOrOperationAValue = (sourceOrOperation: any): boolean => {
    if (!sourceOrOperation.hasOwnProperty('value')) {
      return false;
    }

    return (
      !sourceOrOperation.hasOwnProperty('field') ||
      typeof sourceOrOperation.value !== 'undefined'
    );
  };

  return (
    <>
      <ul
        className={`AknRuleOperation${
          isFormFieldInError('type') ? ' AknRuleOperation--error' : ''
        }`}
        ref={dragulaDecorator}>
        {fields &&
          fields.map((sourceOrOperation: any, operationLineNumber) => {
            return (
              <CalculateOperationLine
                key={sourceOrOperation.id}
                baseFormName={`full_operation_list[${operationLineNumber}]`}
                sourceOrOperation={sourceOrOperation}
                locales={locales}
                uiLocales={uiLocales}
                scopes={scopes}
                lineNumber={lineNumber}
                operationLineNumber={operationLineNumber}
                removeOperation={removeOperation}
                isValue={isSourceOrOperationAValue(sourceOrOperation)}
              />
            );
          })}
      </ul>
      <div className={'AknButtonList AknButtonList--single'}>
        <BlueGhostButton
          data-testid={`edit-rules-action-${lineNumber}-add-value`}
          onClick={(e: any) => {
            if (e) {
              e.preventDefault();
            }
            handleAddValue();
          }}
          className={'AknButtonList-item'}>
          {translate(
            'pimee_catalog_rule.form.edit.actions.calculate.add_value'
          )}
        </BlueGhostButton>
        <div className={'AknButtonList-item'}>
          <AddFieldButton
            data-testid={`edit-rules-action-${lineNumber}-add-attribute`}
            handleAddField={handleAddAttribute}
            isFieldAlreadySelected={() => false}
            filterSystemFields={[]}
            filterAttributeTypes={[
              AttributeType.NUMBER,
              AttributeType.PRICE_COLLECTION,
              AttributeType.METRIC,
            ]}
            containerCssClass={'add-attribute-button'}
            dropdownCssClass={'add-attribute-dropdown'}
            placeholder={translate(
              'pimee_catalog_rule.form.edit.actions.calculate.add_attribute'
            )}
          />
        </div>
      </div>
    </>
  );
};

export {CalculateOperationList};
