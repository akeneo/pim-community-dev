import React from 'react';
import {SectionTitle, SettingsIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {NoDataSection, NoDataText} from '@akeneo-pim-community/shared';

const LastOperationsWidget = () => {
  const translate = useTranslate();

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_dashboard.widget.last_operations.title')}</SectionTitle.Title>
      </SectionTitle>

      <NoDataSection style={{marginTop: 0}}>
        <SettingsIllustration width={128} height={128} />
        <NoDataText>{translate('pim_import_export.widget.last_operations.empty')}</NoDataText>
      </NoDataSection>
    </>
  );
};

export {LastOperationsWidget};
