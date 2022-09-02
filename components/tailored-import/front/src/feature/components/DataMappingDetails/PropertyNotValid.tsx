import React from 'react';
import {Placeholder, RulesIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const PropertyNotValid = () => {
  const translate = useTranslate();

  return (
    <Placeholder
      illustration={<RulesIllustration />}
      title={translate('akeneo.tailored_import.data_mapping.property_not_valid')}
    />
  );
};

export {PropertyNotValid};
