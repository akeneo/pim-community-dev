import React from 'react'
import { Controller } from 'react-hook-form'
import { ConditionLineProps } from './ConditionLineProps'
import { IdentifierAttributeCondition, IdentifierAttributeOperators, Attribute } from '../../../../models'
import { useBackboneRouter, useTranslate } from '../../../../dependenciesTools/hooks'
import { useControlledFormInputCondition } from '../../hooks'
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository'
import { AttributeConditionLine } from './AttributeConditionLine'
import { InputText } from '../../../../components'

type IdentifierAttributeConditionLineProps = ConditionLineProps & {
  condition: IdentifierAttributeCondition;
}

const IdentifierAttributeConditionLine: React.FC<IdentifierAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter()
  const translate = useTranslate()

  const {
    valueFormName,
    getValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<string[]>(lineNumber)

  const [attribute, setAttribute] = React.useState<Attribute | null>()

  React.useEffect(() => {
    getAttributeByIdentifier(condition.field, router).then(attribute => setAttribute(attribute))
  }, [])

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
    >
      <Controller
        as={InputText}
        className={
          isFormFieldInError('value')
          ? 'AknTextField AknTextField--error'
          : undefined
        }
        data-testid={`edit-rules-input-${lineNumber}-value`}
        name={valueFormName}
        label={translate('pimee_catalog_rule.rule.value')}
        hiddenLabel
        defaultValue={getValueFormValue()}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required')
        }}
      />
    </AttributeConditionLine>
  )
}

export {
  IdentifierAttributeConditionLine,
  IdentifierAttributeConditionLineProps,
}
