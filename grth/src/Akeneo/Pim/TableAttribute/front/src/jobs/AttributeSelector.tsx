import React from 'react';
import {Attribute, AttributeCode, AttributeType} from '../models';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeFetcher, AttributeFetcherIndexParams} from '../fetchers';

type AttributeSelectorProps = {
  label: string;
  readOnly?: boolean;
  initialValue: AttributeCode | null;
  onChange: (attributeCode: AttributeCode | null) => void;
  errorMessage: string | null;
  required?: boolean;
  types?: AttributeType[];
};

const AttributeSelector: React.FC<AttributeSelectorProps> = ({
  label,
  readOnly = false,
  initialValue = null,
  onChange,
  errorMessage = null,
  required = false,
  types,
}) => {
  const router = useRouter();
  const userContext = useUserContext();
  const translate = useTranslate();
  const catalogLocale = userContext.get('catalogLocale');
  const [attributes, setAttributes] = React.useState<Attribute[] | undefined>();
  const [value, setValue] = React.useState<AttributeCode | null>(initialValue);
  const [search, setSearch] = React.useState<string>('');

  React.useEffect(() => {
    const params: AttributeFetcherIndexParams = {search};
    if (typeof types !== 'undefined') {
      params.types = types;
    }
    AttributeFetcher.query(router, params).then(attributes => setAttributes(attributes));
  }, [router, types, search]);

  const handleChange = (attributeCode: AttributeCode | null) => {
    onChange(attributeCode);
    setValue(attributeCode);
  };

  return (
    <Field label={label} requiredLabel={required ? translate('pim_common.required_label') : undefined}>
      <SelectInput
        value={value}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={'pim_common.open'}
        onChange={handleChange}
        readOnly={readOnly}
        onSearchChange={setSearch}
        clearLabel={translate('pim_common.clear')}>
        {(attributes || []).map(attribute => (
          <SelectInput.Option value={attribute.code} key={attribute.code}>
            {getLabel(attribute.labels, catalogLocale, attribute.code)}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {!!errorMessage && <Helper level='error'>{errorMessage}</Helper>}
    </Field>
  );
};

export {AttributeSelector};
