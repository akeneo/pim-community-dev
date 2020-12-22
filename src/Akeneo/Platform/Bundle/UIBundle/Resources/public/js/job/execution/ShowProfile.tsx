import React from 'react';
import {Link} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge/src';
import {JobInstance} from './Report';

const ShowProfile = ({jobInstance}: {jobInstance: JobInstance}) => {
  if (!['import', 'export'].includes(jobInstance.type)) return null;

  const router = useRouter();
  var route = 'pim_importexport_%type%_profile_show'.replace('%type%', jobInstance.type);
  const href = `#/${router.generate(route, {code: jobInstance.code})}`;

  const translate = useTranslate();

  return <Link href={href}>{translate('pim_import_export.form.job_execution.button.show_profile.title')}</Link>;
};

export {ShowProfile};
