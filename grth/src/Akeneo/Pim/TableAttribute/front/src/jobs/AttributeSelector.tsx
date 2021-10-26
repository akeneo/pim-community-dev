import React from 'react';
import {AttributeCode, TableAttribute} from "../models";
import {Field, Helper, SelectInput} from "akeneo-design-system";
import {AttributeRepository} from "../repositories";
import {getLabel, useRouter, useTranslate, useUserContext} from "@akeneo-pim-community/shared";

type AttributeSelectorProps = {
  label: string;
  readOnly: boolean;
  initialValue: AttributeCode | null;
  onChange: (attributeCode: AttributeCode | null) => void;
  errorMessage: string | null;
};

const AttributeSelector: React.FC<AttributeSelectorProps> = ({
  label,
  readOnly,
  initialValue,
  onChange,
  errorMessage,
}) => {
  const router = useRouter();
  const userContext = useUserContext();
  const translate = useTranslate();
  const catalogLocale = userContext.get('catalogLocale');
  const [attributes, setAttributes] = React.useState<TableAttribute[] | undefined>();
  const [value, setValue] = React.useState<AttributeCode | null>(initialValue);

  React.useEffect(() => {
    AttributeRepository.findAllByTypes(router, ['pim_catalog_table']).then(attributes => setAttributes(attributes as TableAttribute[]));
  }, []);

  const handleChange = (attributeCode: AttributeCode | null) => {
    onChange(attributeCode);
    setValue(attributeCode);
  }

  return <Field label={label}>
    <SelectInput
      value={value}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={'pim_common.open'}
      onChange={handleChange}
      readOnly={readOnly}
    >
      {(attributes || []).map(attribute => <SelectInput.Option value={attribute.code} key={attribute.code}>
        {getLabel(attribute.labels, catalogLocale, attribute.code)}
      </SelectInput.Option>)}
    </SelectInput>
    {!!errorMessage && <Helper level="error">{errorMessage}</Helper>}
  </Field>
}

export {AttributeSelector}
