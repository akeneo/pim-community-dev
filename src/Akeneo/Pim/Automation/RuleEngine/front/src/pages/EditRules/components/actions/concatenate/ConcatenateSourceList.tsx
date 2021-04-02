// eslint-disable-next-line @typescript-eslint/no-var-requires
const Dragula = require('react-dragula');
import React from 'react';
import {useFieldArray, useFormContext} from 'react-hook-form';
import {Operation} from '../../../../../models/actions/Calculate/Operation';
import {useTranslate} from '../../../../../dependenciesTools/hooks';
import {
  Attribute,
  attributeAcceptsNewLine,
  AttributeCode,
  AttributeType,
  Locale,
} from '../../../../../models';
import {IndexedScopes} from '../../../../../repositories/ScopeRepository';
import {useControlledFormInputAction} from '../../../hooks';
import {AddFieldButton} from '../../../../../components/Selectors/AddFieldButton';
import {ConcatenateSourceLine} from './ConcatenateSourceLine';
import {
  BlueGhostButton,
  GreyGhostButton,
} from '../../../../../components/Buttons';

type Props = {
  lineNumber: number;
  onChange?: (value: Operation[]) => void;
  locales: Locale[];
  uiLocales: Locale[];
  scopes: IndexedScopes;
  attributeTarget: Attribute | null;
};

const ConcatenateSourceList: React.FC<Props> = ({
  lineNumber,
  locales,
  uiLocales,
  scopes,
  attributeTarget,
}) => {
  const translate = useTranslate();
  const {formName, isFormFieldInError} = useControlledFormInputAction<
    string | null
  >(lineNumber);
  const {watch} = useFormContext();
  const [
    operationLineToRemoveStack,
    setOperationLineToRemoveStack,
  ] = React.useState<number[]>([]);

  const {fields, remove, move, append} = useFieldArray({
    name: formName('from'),
  });

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

  const handleAddAttribute = (attributeCode: AttributeCode) => {
    append({field: attributeCode});
  };
  const handleAddText = (text: string) => {
    append({text});
  };
  const handleAddNewLine = () => {
    append({new_line: null});
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
          const fields: [] = watch(formName('from')) ?? [];
          let target = Number(sibling?.dataset?.lineNumber ?? fields.length);
          if (target > origin) {
            target--;
          }
          moveOperation(origin, target);
        }
      );
    }
  }, [dragulaDecorator]);

  React.useEffect(() => {
    if (attributeTarget && !attributeAcceptsNewLine(attributeTarget)) {
      const operationLineNumberToRemove: number[] = [];
      fields.map((sourceOrOperation: any, operationLineNumber) => {
        if ('undefined' !== typeof sourceOrOperation?.new_line) {
          operationLineNumberToRemove.push(operationLineNumber);
        }
      });
      setOperationLineToRemoveStack(operationLineNumberToRemove);
    }
  }, [attributeTarget]);

  // We cannot remove multiple line in one render. We need to remove them one by one.
  // See https://react-hook-form.com/v5/api#useFieldArray
  React.useEffect(() => {
    if (!operationLineToRemoveStack.length) {
      return;
    }
    remove(operationLineToRemoveStack.pop());
    setOperationLineToRemoveStack(operationLineToRemoveStack);
  }, [JSON.stringify(operationLineToRemoveStack)]);

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
              <ConcatenateSourceLine
                key={sourceOrOperation.id}
                baseFormName={`from[${operationLineNumber}]`}
                source={sourceOrOperation}
                locales={locales}
                uiLocales={uiLocales}
                scopes={scopes}
                lineNumber={lineNumber}
                operationLineNumber={operationLineNumber}
                removeOperation={removeOperation}
                isValue={
                  sourceOrOperation.hasOwnProperty('value') &&
                  typeof sourceOrOperation.value !== 'undefined'
                }
              />
            );
          })}
      </ul>
      <div className={'AknButtonList AknButtonList--single'}>
        <BlueGhostButton
          data-testid={`edit-rules-action-${lineNumber}-add-text`}
          onClick={(e: any) => {
            if (e) {
              e.preventDefault();
            }
            handleAddText('');
          }}
          className={'AknButtonList-item'}>
          {translate(
            'pimee_catalog_rule.form.edit.actions.concatenate.add_text'
          )}
        </BlueGhostButton>
        <div className={'AknButtonList-item'}>
          <AddFieldButton
            data-testid={`edit-rules-action-${lineNumber}-add-attribute`}
            handleAddField={handleAddAttribute}
            isFieldAlreadySelected={() => false}
            filterSystemFields={[]}
            filterAttributeTypes={[
              AttributeType.TEXT,
              AttributeType.OPTION_SIMPLE_SELECT,
              AttributeType.OPTION_MULTI_SELECT,
              AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
              AttributeType.REFERENCE_ENTITY_COLLECTION,
              AttributeType.NUMBER,
              AttributeType.IDENTIFIER,
              AttributeType.DATE,
              AttributeType.PRICE_COLLECTION,
              AttributeType.METRIC,
              AttributeType.TEXTAREA,
            ]}
            containerCssClass={'add-attribute-button'}
            dropdownCssClass={'add-attribute-dropdown'}
            placeholder={translate(
              'pimee_catalog_rule.form.edit.actions.concatenate.add_attribute'
            )}
          />
        </div>
        {!attributeTarget ||
          (attributeAcceptsNewLine(attributeTarget) && (
            <GreyGhostButton
              data-testid={`edit-rules-action-${lineNumber}-add-new-line`}
              onClick={(e: any) => {
                if (e) {
                  e.preventDefault();
                }
                handleAddNewLine();
              }}
              className={'AknButtonList-item'}>
              {translate(
                'pimee_catalog_rule.form.edit.actions.concatenate.add_new_line'
              )}
            </GreyGhostButton>
          ))}
      </div>
    </>
  );
};

export {ConcatenateSourceList};
