import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type EmptyAttributeOptionsListProps = {
  showNewOptionForm: (isDisplayed: boolean) => void;
};

const EmptyAttributeOptionsList = ({showNewOptionForm}: EmptyAttributeOptionsListProps) => {
  const translate = useTranslate();

  return (
    <div className="AknAttributeOption-emptyList">
      <img src="/bundles/pimui/images/illustrations/Attribute.svg" />
      <div className="AknAttributeOption-emptyList-message">
        {translate('pim_enrich.entity.attribute_option.module.edit.no_options_msg')}
      </div>
      <div
        className="AknAttributeOption-emptyList-addLink"
        onClick={() => showNewOptionForm(true)}
        role="add-new-attribute-option-button"
      >
        {translate('pim_enrich.entity.attribute_option.module.edit.add_option')}
      </div>
    </div>
  );
};

export default EmptyAttributeOptionsList;
