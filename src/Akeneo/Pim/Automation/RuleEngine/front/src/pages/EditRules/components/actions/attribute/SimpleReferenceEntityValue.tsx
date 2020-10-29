import React from 'react';
import {
  useTranslate,
  useUserCatalogLocale,
  useUserCatalogScope,
} from '../../../../../dependenciesTools/hooks';
import {InputValueProps} from './AttributeValue';
import {getAttributeLabel} from '../../../../../models';
import {Label} from '../../../../../components/Labels';
import {ReferenceEntitySelector} from '../../../../../dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector';

const SimpleReferenceEntityValue: React.FC<InputValueProps> = ({
  attribute,
  value,
  label,
  onChange,
}) => {
  const currentCatalogLocale = useUserCatalogLocale();
  const currentCatalogScope = useUserCatalogScope();
  const translate = useTranslate();

  return (
    <>
      <Label
        label={label || getAttributeLabel(attribute, currentCatalogLocale)}
      />
      <ReferenceEntitySelector
        placeholder={translate(
          'pimee_catalog_rule.form.edit.actions.set_attribute.select_reference_entity'
        )}
        compact={true}
        readOnly={false}
        onChange={onChange}
        channel={currentCatalogScope}
        locale={currentCatalogLocale}
        value={value}
        referenceEntityIdentifier={attribute.reference_data_name as string}
        multiple={false}
        dropdownCssClass={'record-selector-dropdown--rules'}
      />
    </>
  );
};

export {SimpleReferenceEntityValue};
