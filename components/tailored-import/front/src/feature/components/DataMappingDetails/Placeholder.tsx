import React from 'react';
import {Placeholder, RulesIllustration} from "akeneo-design-system";
import {useTranslate} from "@akeneo-pim-community/shared";

const AttributeDoesNotExist = () => {
  const translate = useTranslate();

  return (
    <Placeholder
      illustration={<RulesIllustration />}
      title={translate('akeneo.tailored_import.data_mapping.attribute_not_found')}
    />
  )
}

export { AttributeDoesNotExist };
