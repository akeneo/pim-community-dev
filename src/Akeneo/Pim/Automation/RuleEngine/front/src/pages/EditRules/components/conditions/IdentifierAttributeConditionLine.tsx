import React from 'react';
import {Controller} from 'react-hook-form';
import {ConditionLineProps} from './ConditionLineProps';
import {
  Attribute,
  IdentifierAttributeCondition,
  IdentifierAttributeOperators,
} from '../../../../models';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import {useControlledFormInputCondition} from '../../hooks';
import {AttributeConditionLine} from './AttributeConditionLine';
import {InputText} from '../../../../components';
import {useGetAttributeAtMount} from '../actions/attribute/attribute.utils';
import {Operator} from '../../../../models/Operator';
import {
  Identifier,
  IdentifiersSelector,
} from '../../../../components/Selectors/IdentifiersSelector';

type IdentifierAttributeConditionLineProps = ConditionLineProps & {
  condition: IdentifierAttributeCondition;
};

const IdentifierAttributeConditionLine: React.FC<IdentifierAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();

  const {
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
    setValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<string | string[]>(lineNumber);

  const [attribute, setAttribute] = React.useState<Attribute | null>();
  useGetAttributeAtMount(condition.field, router, attribute, setAttribute);

  const shouldDisplaySelector: (operator: Operator) => boolean = operator => {
    return ([Operator.IN_LIST, Operator.NOT_IN_LIST] as Operator[]).includes(
      operator
    );
  };

  const [displaySelector, setDisplaySelector] = React.useState(
    shouldDisplaySelector(getOperatorFormValue())
  );
  React.useEffect(() => {
    const operatorFormValue = getOperatorFormValue();
    if (displaySelector && !shouldDisplaySelector(operatorFormValue)) {
      setValueFormValue('');
      setDisplaySelector(false);
    } else if (!displaySelector && shouldDisplaySelector(operatorFormValue)) {
      setValueFormValue([]);
      setDisplaySelector(true);
    }
  }, [getOperatorFormValue()]);

  return (
    <AttributeConditionLine
      attribute={attribute}
      availableOperators={IdentifierAttributeOperators}
      currentCatalogLocale={currentCatalogLocale}
      defaultOperator={IdentifierAttributeOperators[0]}
      field={condition.field}
      lineNumber={lineNumber}
      locales={locales}
      scopes={scopes}
      valueHasError={isFormFieldInError('value')}>
      <Controller
        as={<input type='hidden' />}
        name={valueFormName}
        defaultValue={getValueFormValue()}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required'),
          validate: (value: any) =>
            displaySelector && Array.isArray(value) && value.length === 0
              ? translate('pimee_catalog_rule.exceptions.required')
              : true,
        }}
      />
      {displaySelector ? (
        <IdentifiersSelector
          id={`edit-rules-input-${lineNumber}-value-selector`}
          data-testid={`edit-rules-input-${lineNumber}-value-selector`}
          name={valueFormName}
          label={translate('pimee_catalog_rule.rule.value')}
          hiddenLabel
          value={getValueFormValue() as Identifier[]}
          entityType={'product'}
          onChange={setValueFormValue}
        />
      ) : (
        <InputText
          className={
            isFormFieldInError('value')
              ? 'AknTextField AknTextField--error'
              : undefined
          }
          id={`edit-rules-input-${lineNumber}-value-text`}
          data-testid={`edit-rules-input-${lineNumber}-value-text`}
          name={valueFormName}
          label={translate('pimee_catalog_rule.rule.value')}
          hiddenLabel
          value={getValueFormValue() as string}
          onChange={event => setValueFormValue(event.target.value)}
        />
      )}
    </AttributeConditionLine>
  );
};

export {
  IdentifierAttributeConditionLine,
  IdentifierAttributeConditionLineProps,
};
