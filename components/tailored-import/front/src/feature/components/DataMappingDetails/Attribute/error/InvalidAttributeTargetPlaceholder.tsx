import React from 'react';
import {Placeholder, RulesIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const InvalidAttributeTargetPlaceholder = () => {
  const translate = useTranslate();

  return (
    <Placeholder
      title={translate('akeneo.tailored_import.data_mapping.target.invalid.attribute')}
      illustration={<RulesIllustration />}
    />
  );
};

export {InvalidAttributeTargetPlaceholder};
