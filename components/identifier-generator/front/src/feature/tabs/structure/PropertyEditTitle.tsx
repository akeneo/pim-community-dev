import React from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {PROPERTY_NAMES} from '../../models';

type Props = {type: PROPERTY_NAMES};

const PropertyEditTitle: React.FC<Props> = ({type}) => {
  const translate = useTranslate();

  return (
    <SectionTitle>
      <SectionTitle.Title>{translate(`pim_identifier_generator.structure.settings.${type}.title`)}</SectionTitle.Title>
    </SectionTitle>
  );
};

export {PropertyEditTitle};
