import React from 'react';
import {ConditionLineProps} from './ConditionLineProps';
import {AttributeConditionLine} from './AttributeConditionLine';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
  useUserCatalogScope,
} from '../../../../dependenciesTools/hooks';
import {Attribute} from '../../../../models';
import {Operator} from '../../../../models/Operator';
import {
  AssetCollectionAttributeCondition,
  AssetCollectionAttributeOperators,
} from '../../../../models/conditions';
import {Controller} from 'react-hook-form';
import {useControlledFormInputCondition} from '../../hooks';
import {AssetSelector} from '../../../../dependenciesTools/components/AssetManager/AssetSelector';
import {useGetAttributeAtMount} from '../actions/attribute/attribute.utils';

type AssetCollectionAttributeConditionLineProps = ConditionLineProps & {
  condition: AssetCollectionAttributeCondition;
};

const AssetCollectionAttributeConditionLine: React.FC<AssetCollectionAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const userCatalogLocale = useUserCatalogLocale();
  const userCatalogScope = useUserCatalogScope();

  const {
    valueFormName,
    getValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  const [attribute, setAttribute] = React.useState<Attribute | null>();
  useGetAttributeAtMount(condition.field, router, attribute, setAttribute);

  if (!attribute) {
    return (
      <img
        src='/bundles/pimui/images//loader-V2.svg'
        alt={translate('pim_common.loading')}
      />
    );
  }

  const val = getValueFormValue() || null;

  return (
    <AttributeConditionLine
      defaultOperator={Operator.IS_EMPTY}
      field={condition.field}
      lineNumber={lineNumber}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
      availableOperators={AssetCollectionAttributeOperators}
      attribute={attribute}
      valueHasError={isFormFieldInError('value')}>
      {attribute && (
        <Controller
          as={AssetSelector}
          value={val}
          assetFamilyIdentifier={attribute.reference_data_name as string}
          locale={userCatalogLocale}
          channel={userCatalogScope}
          placeholder={translate(
            'pimee_catalog_rule.form.edit.actions.set_attribute.select_asset'
          )}
          compact={true}
          multiple={true}
          name={valueFormName}
          rules={{
            required: translate('pimee_catalog_rule.exceptions.required'),
            validate: (assetCodes: string[]) =>
              Array.isArray(assetCodes) && assetCodes.length === 0
                ? translate('pimee_catalog_rule.exceptions.required')
                : true,
          }}
          dropdownCssClass={'asset-selector-dropdown--rules'}
        />
      )}
    </AttributeConditionLine>
  );
};

export {
  AssetCollectionAttributeConditionLine,
  AssetCollectionAttributeConditionLineProps,
};
