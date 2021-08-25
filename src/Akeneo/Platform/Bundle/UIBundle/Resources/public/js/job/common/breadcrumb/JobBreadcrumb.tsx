import {useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb} from 'akeneo-design-system';
import React from 'react';

type JobBreadcrumbProps = {jobLabel: string, jobType: string};

const JobBreadcrumb = ({jobLabel, jobType}: JobBreadcrumbProps) => {
  const jobIndexUrl = useRoute(`pim_importexport_${jobType}_profile_index`);
  const translate = useTranslate();

  return (
    <Breadcrumb>
      <Breadcrumb.Step href={`#${jobIndexUrl}`}>
        {translate(`pim_menu.tab.${jobType}s`)}
      </Breadcrumb.Step>
      <Breadcrumb.Step>
        {jobLabel}
      </Breadcrumb.Step>
    </Breadcrumb>
  )
}

export {JobBreadcrumb}
