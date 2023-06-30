import React from 'react';
import {Button, ButtonProps} from 'akeneo-design-system';
import {useRoute} from '@akeneo-pim-community/shared';
import {JobStatus} from '../../models';

type PauseResumeJobActionProps = {
  id: string;
  status: JobStatus;
  onPauseResume: () => void;
} & ButtonProps;

const PauseResumeJobAction = ({id, status, onPauseResume, ...rest}: PauseResumeJobActionProps) => {
  const pauseRoute = useRoute('akeneo_job_pause_job_execution_action', {id});
  const resumeRoute = useRoute('akeneo_job_resume_job_execution_action', {id});

  const isPaused = 'PAUSED' === status;

  const handlePauseResume = async () => {
    const route = isPaused ? resumeRoute : pauseRoute;
    await fetch(route, {method: 'POST'});
    onPauseResume();
  };

  return (
    <Button onClick={handlePauseResume} level={isPaused ? 'secondary' : 'warning'} {...rest}>
      {isPaused ? 'resume' : 'pause'}
    </Button>
  );
};

export {PauseResumeJobAction};
