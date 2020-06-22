import React from 'react';
import { useFormContext, Controller } from 'react-hook-form';
import { Attribute } from '../../../../../models';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { TextValue } from './TextValue';
import { FallbackValue } from './FallbackValue';
import { SimpleSelectValue } from './SimpleSelectValue';
import { InlineHelper } from '../../../../../components/HelpersInfos';
import { ActionFormContainer } from '../style';
import styled from 'styled-components';

const HelperContainer = styled.div`
  margin-top: 15px;
`;

const MANAGED_ATTRIBUTE_TYPES: { [key: string]: React.FC<InputValueProps> } = {
  pim_catalog_text: TextValue,
  pim_catalog_simpleselect: SimpleSelectValue,
};

type InputValueProps = {
  id: string;
  attribute: Attribute;
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
  value: any;
  label?: string;
  onChange: (value: any) => void;
};

const getValueModule: (
  attribute: Attribute
) => React.FC<InputValueProps> | null = attribute => {
  return MANAGED_ATTRIBUTE_TYPES[attribute.type] || null;
};

enum AttributeStatus {
  NOT_SELECTED,
  UNKNOWN,
  UNMANAGED,
  VALID,
}

type Props = {
  id: string;
  attribute?: Attribute | null;
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
  value: any;
  label?: string;
};

const AttributeValue: React.FC<Props> = ({
  id,
  attribute,
  name,
  validation,
  value,
  label,
}) => {
  const translate = useTranslate();
  const { setValue, unregister, watch } = useFormContext();
  const [ValueModule, setValueModule] = React.useState<React.FC<
    InputValueProps
  > | null>(null);
  const [lastKnownValue, setLastKnownValue] = React.useState<any>(value);
  const previousAttribute = React.useRef<Attribute | null | undefined>();

  React.useEffect(() => {
    return () => {
      unregister(name);
    };
  }, []);

  React.useEffect(() => {
    if (undefined !== previousAttribute.current) {
      setValue(name, null);
      setLastKnownValue(null);
    }

    previousAttribute.current = attribute;
    setValueModule(() => (attribute ? getValueModule(attribute) : null));
  }, [attribute]);

  watch(name);

  const getAttributeStatus = (): AttributeStatus => {
    if (undefined === attribute) {
      return AttributeStatus.NOT_SELECTED;
    }

    if (null === attribute) {
      return AttributeStatus.UNKNOWN;
    }

    if (attribute && ValueModule) {
      return AttributeStatus.VALID;
    }

    return AttributeStatus.UNMANAGED;
  };

  return (
    <ActionFormContainer>
      <Controller
        as={<span style={{ display: 'none' }} />}
        name={name}
        defaultValue={value}
        rules={validation || {}}
      />
      {getAttributeStatus() === AttributeStatus.NOT_SELECTED && (
        <InlineHelper>
          {translate('pimee_catalog_rule.form.edit.please_select_attribute')}
        </InlineHelper>
      )}
      {getAttributeStatus() === AttributeStatus.UNKNOWN && (
        <FallbackValue id={id} label={label} value={lastKnownValue} />
      )}
      {getAttributeStatus() === AttributeStatus.UNMANAGED && (
        <FallbackValue id={id} label={label} value={lastKnownValue}>
          <HelperContainer>
            <InlineHelper>
              {translate(
                'pimee_catalog_rule.form.edit.unhandled_attribute_type'
              )}
            </InlineHelper>
          </HelperContainer>
        </FallbackValue>
      )}
      {getAttributeStatus() === AttributeStatus.VALID &&
        ValueModule &&
        attribute && (
          <ValueModule
            id={id}
            attribute={attribute}
            name={name}
            label={label}
            value={lastKnownValue}
            onChange={(value: any) => {
              setValue(name, value);
              setLastKnownValue(value);
            }}
          />
        )}
    </ActionFormContainer>
  );
};

export { AttributeValue, InputValueProps, MANAGED_ATTRIBUTE_TYPES };
