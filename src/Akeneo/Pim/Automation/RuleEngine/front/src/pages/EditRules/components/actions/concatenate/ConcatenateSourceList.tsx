// eslint-disable-next-line @typescript-eslint/no-var-requires
const Dragula = require('react-dragula');
import React from 'react';
import { useFieldArray } from 'react-hook-form';
import { Operation } from '../../../../../models/actions/Calculate/Operation';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { AttributeCode, AttributeType, Locale } from '../../../../../models';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import { useControlledFormInputAction } from '../../../hooks';
import { AddFieldButton } from '../../../../../components/Selectors/AddFieldButton';
import { ConcatenateSourceLine } from './ConcatenateSourceLine';

type Props = {
  lineNumber: number;
  onChange?: (value: Operation[]) => void;
  locales: Locale[];
  scopes: IndexedScopes;
};

const ConcatenateSourceList: React.FC<Props> = ({
  lineNumber,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const { formName, isFormFieldInError } = useControlledFormInputAction<
    string | null
  >(lineNumber);

  const { fields, remove, move, append } = useFieldArray({
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
    append({ field: attributeCode });
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
          let target = Number(sibling?.dataset?.lineNumber || fields.length);
          if (target > origin) {
            target--;
          }
          moveOperation(origin, target);
        }
      );
    }
  }, [dragulaDecorator]);

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
              'pimee_catalog_rule.form.edit.actions.calculate.add_attribute'
            )}
          />
        </div>
      </div>
    </>
  );
};

export { ConcatenateSourceList };
