import React, {ChangeEvent, FC, useEffect, useRef} from 'react';

import {AttributeOption, Locale} from '../model';
import {useEditingOptionContext} from '../contexts';

type AttributeOptionFormProps = {
  option: AttributeOption;
  locale: Locale;
  onUpdateOptionLabel: (event: ChangeEvent, localeCode: string) => void;
};

const AttributeOptionForm: FC<AttributeOptionFormProps> = ({option, locale, onUpdateOptionLabel}) => {
  const inputRef = useRef<HTMLInputElement>(null);
  const {addRef, removeRef} = useEditingOptionContext();

  useEffect(() => {
    addRef(locale.code, inputRef);

    return () => {
      removeRef(locale.code, inputRef);
    };
  }, [inputRef, addRef]);

  return (
    <div className="AknFieldContainer">
      <div className="AknFieldContainer-header">
        <label className="AknFieldContainer-label control-label AknFieldContainer-label">{locale.label}</label>
      </div>
      <div className="AknFieldContainer-inputContainer field-input">
        <input
          ref={inputRef}
          type="text"
          className="AknTextField"
          defaultValue={option.optionValues[locale.code].value}
          role="attribute-option-label"
          onChange={(event: ChangeEvent<HTMLInputElement>) => onUpdateOptionLabel(event, locale.code)}
          data-locale={locale.code}
        />
      </div>
    </div>
  );
};

export default AttributeOptionForm;
