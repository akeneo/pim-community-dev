// eslint-disable-next-line @typescript-eslint/no-var-requires
const RecordSelector = require('akeneoreferenceentity/application/component/app/record-selector')
  .default;
import * as React from 'react';

export default class ReferenceEntityString {
  private constructor(private code: string) {
    Object.freeze(this);
  }

  public static create(code: string): ReferenceEntityString {
    return new ReferenceEntityString(code);
  }

  public equals(code: ReferenceEntityString): boolean {
    return this.stringValue() === code.stringValue();
  }

  public stringValue(): string {
    return this.code;
  }

  public normalize(): string {
    return this.code;
  }
}

type ReferenceEntityIdentifier = string;

export type RecordSelectorProps = {
  value: string[] | string | null;
  referenceEntityIdentifier: ReferenceEntityIdentifier;
  multiple?: boolean;
  readOnly?: boolean;
  compact?: boolean;
  locale: string | null;
  channel: string | null;
  placeholder: string;
  onChange: (value: string[] | string | null) => void;
  dropdownCssClass?: string;
};

export const ReferenceEntitySelector: React.FC<RecordSelectorProps> = ({
  value,
  referenceEntityIdentifier,
  multiple,
  readOnly,
  compact,
  locale,
  channel,
  placeholder,
  onChange,
  dropdownCssClass,
}) => {
  const handleChange = (
    value: ReferenceEntityString | ReferenceEntityString[] | null
  ) => {
    if (Array.isArray(value)) {
      onChange(value.map(subValue => subValue.stringValue()));
    } else if (value) {
      onChange(value.stringValue());
    } else {
      onChange(multiple ? [] : null);
    }
  };

  const createValue = (value: string | string[] | null) => {
    if (Array.isArray(value)) {
      return value.map(subValue => ReferenceEntityString.create(subValue));
    } else if (value) {
      return ReferenceEntityString.create(value);
    } else {
      return multiple ? [] : null;
    }
  };

  return (
    <RecordSelector
      value={createValue(value)}
      referenceEntityIdentifier={ReferenceEntityString.create(
        referenceEntityIdentifier
      )}
      multiple={multiple}
      readOnly={readOnly}
      compact={compact}
      locale={locale ? ReferenceEntityString.create(locale) : null}
      channel={channel ? ReferenceEntityString.create(channel) : null}
      placeholder={placeholder}
      onChange={handleChange}
      dropdownCssClass={dropdownCssClass}
    />
  );
};
