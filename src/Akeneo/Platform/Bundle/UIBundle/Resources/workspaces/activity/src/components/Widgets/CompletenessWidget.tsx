import React from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const CompletenessWidget = () => {
  const translate = useTranslate();

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_dashboard.widget.completeness.title')}</SectionTitle.Title>
      </SectionTitle>

      Completeness content
    </>
  );
};

export {CompletenessWidget};
