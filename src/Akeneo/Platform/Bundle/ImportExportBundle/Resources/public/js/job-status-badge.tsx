const Routing = require('routing');
import React, {useEffect, useState} from "react";

const JobStatusBadge = ({jobId, status}: { jobId: string; status: string }) => {
  const [jobStatus, setStatus] = useState({
    status: status,
    currentStep: 0,
    totalSteps: 0
  });

  useEffect(() => {
    const fetchData = async () => {
      const response = await fetch(
        Routing.generate(
          'pim_enrich_job_execution_progress_rest_get',
          {jobId}),
        {
          method: 'GET',
          headers: [
            ['Content-type', 'application/json'],
            ['X-Requested-With', 'XMLHttpRequest'],
          ]
        }
      );
      const newJobStatus = await response.json();
      setStatus({
        ...jobStatus,
        currentStep: newJobStatus.currentStep,
        totalSteps: newJobStatus.totalSteps,
      });
    }
    fetchData()
  }, []);

  return <div>{jobStatus.status === 'STARTED' ? `${jobStatus.status} ${jobStatus.currentStep}/${jobStatus.totalSteps}` : jobStatus.status}</div>
}

export = JobStatusBadge;
