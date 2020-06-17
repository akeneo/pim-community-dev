import React from 'react';
import { useFormContext } from 'react-hook-form';
import { Attribute } from '../../../../../models';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { supportsTextValueModule } from './TextValue';
import { FallbackValue } from './FallbackValue';
import { useUnregisterAtUnmount } from '../../../hooks/useUnregisterAtUnmount';

type InputValueProps = {
  id: string;
  attribute: Attribute;
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
  value: any;
  label?: string;
  onChange: (value: any) => void;
};

export type ValueModuleGuesser = (
  attribute: Attribute
) => React.FC<InputValueProps> | null;

const getValueModule: (
  attribute: Attribute
) => React.FC<InputValueProps> = attribute => {
  const getValueModuleFunctions: ValueModuleGuesser[] = [
    supportsTextValueModule,
  ];

  for (let i = 0; i < getValueModuleFunctions.length; i++) {
    const getValueModuleFunction = getValueModuleFunctions[i];
    const module = getValueModuleFunction(attribute);
    if (module !== null) {
      return module;
    }
  }

  return FallbackValue;
};

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
  const { register, setValue } = useFormContext();
  const [ValueModule, setValueModule] = React.useState<React.FC<
    InputValueProps
  > | null>(null);
  const [lastKnownValue, setLastKnownValue] = React.useState<any>(value);
  const previousAttribute = React.useRef<Attribute | null | undefined>();
  useUnregisterAtUnmount(name);

  React.useEffect(() => {
    if (undefined !== previousAttribute.current) {
      setLastKnownValue(null);
    }

    previousAttribute.current = attribute;
    setValueModule(() => (attribute ? getValueModule(attribute) : null));
  }, [attribute]);

  if (null === attribute) {
    register(name);
    setValue(name, value);

    return (
      <div>{translate('pimee_catalog_rule.form.edit.unknown_attribute')}</div>
    );
  }

  if (!ValueModule || undefined === attribute) {
    return (
      <img
        src='/bundles/pimui/images//loader-V2.svg'
        alt={translate('pim_common.loading')}
      />
    );
  }

  return (
    <ValueModule
      id={id}
      attribute={attribute}
      name={name}
      label={label}
      value={lastKnownValue}
      validation={validation}
      onChange={setLastKnownValue}
    />
  );
};

export { AttributeValue, InputValueProps };
