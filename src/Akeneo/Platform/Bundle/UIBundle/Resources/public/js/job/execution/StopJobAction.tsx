import React from 'react';
import {useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Button} from 'akeneo-design-system';

type StopJobActionProps = {
  id: string;
  isStoppable: boolean;
  refresh: () => void;
};

const StopJobAction = ({id, isStoppable, refresh}: StopJobActionProps) => {
  const translate = useTranslate();
  const stopRoute = useRoute('pim_enrich_job_tracker_rest_stop', {id});

  const stopJob = async () => {
    await fetch(stopRoute);
    refresh();
  };

  if (!isStoppable) return null;

  return (
    <Button onClick={stopJob} level="danger">
      {translate('pim_datagrid.action.stop.title')}
    </Button>
  );
};

export {StopJobAction};
