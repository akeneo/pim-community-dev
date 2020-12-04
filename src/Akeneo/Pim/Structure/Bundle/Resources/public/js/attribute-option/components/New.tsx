import React, {useEffect, useRef} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

interface NewProps {
  createAttributeOption: (optionCode: string) => void;
}

const New = ({createAttributeOption}: NewProps) => {
  const translate = useTranslate();
  const newOptionCodeRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (newOptionCodeRef !== null && newOptionCodeRef.current) {
      newOptionCodeRef.current.focus();
    }
  }, []);

  const createNewOptionFromCode = (event: any) => {
    event.preventDefault();
    if (newOptionCodeRef.current !== null && newOptionCodeRef.current.value) {
      createAttributeOption(newOptionCodeRef.current.value.trim());
    }
  };

  return (
    <div className="AknSubsection AknAttributeOption-edit">
      <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
        <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_settings')}</span>
      </div>
      <form onSubmit={(event: any) => createNewOptionFromCode(event)}>
        <div className="AknFieldContainer">
          <div className="AknFieldContainer-header">
            <label className="AknFieldContainer-label control-label AknFieldContainer-label">
              <span>{translate('pim_enrich.entity.attribute_option.module.edit.option_code')}</span>
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer field-input">
            <input type="text" className="AknTextField" role="attribute-option-label" ref={newOptionCodeRef} />
          </div>
        </div>
        <button className="AknButton AknButton--apply save" role="create-option-button" type="submit">
          {translate('pim_common.done')}
        </button>
      </form>
    </div>
  );
};

export default New;
