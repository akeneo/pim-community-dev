import React, {useRef} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useScrollIntoView} from '../hooks/useScrollIntoView';

interface newOptionPlaceholderProps {
  cancelNewOption: () => void;
}

const NewOptionPlaceholder = ({cancelNewOption}: newOptionPlaceholderProps) => {
  const translate = useTranslate();
  const placeholderRef = useRef<HTMLDivElement>(null);

  useScrollIntoView(placeholderRef);

  return (
    <div
      className="AknAttributeOption-listItem AknAttributeOption-listItem--selected"
      role="new-option-placeholder"
      ref={placeholderRef}
    >
      <span className="AknAttributeOption-itemCode AknAttributeOption-itemCode--new">
        <div>
          <span>{translate('pim_enrich.entity.attribute_option.module.edit.new_option_code')}</span>
        </div>
      </span>
      <span
        className="AknAttributeOption-cancel-new-option-icon"
        onClick={() => cancelNewOption()}
        role="new-option-cancel"
      />
    </div>
  );
};

export default NewOptionPlaceholder;
