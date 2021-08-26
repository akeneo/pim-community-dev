import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useRoute, useTranslate} from '@akeneo-pim-community/shared';

type JobBreadcrumbProps = {
  isEdit: boolean;
  jobCode: string;
  jobLabel: string;
  jobType: string;
};

const JobBreadcrumb = ({isEdit, jobCode, jobLabel, jobType}: JobBreadcrumbProps) => {
  const translate = useTranslate();
  const jobIndexUrl = useRoute(`pim_importexport_${jobType}_profile_index`);
  const jobShowUrl = useRoute(`pim_importexport_${jobType}_profile_show`, {code: jobCode});

  return (
    <Breadcrumb>
      <Breadcrumb.Step href={`#${jobIndexUrl}`}>{translate(`pim_menu.tab.${jobType}s`)}</Breadcrumb.Step>
      <Breadcrumb.Step href={`#${jobShowUrl}`}>{jobLabel}</Breadcrumb.Step>
      {isEdit && <Breadcrumb.Step>{translate('pim_common.edit')}</Breadcrumb.Step>}
    </Breadcrumb>
  );
};

export {JobBreadcrumb};
export type {JobBreadcrumbProps};
