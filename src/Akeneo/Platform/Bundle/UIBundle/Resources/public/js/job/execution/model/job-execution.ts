type StepExecutionStatus =
  | 'COMPLETED'
  | 'STARTING'
  | 'STARTED'
  | 'STOPPING'
  | 'STOPPED'
  | 'FAILED'
  | 'ABANDONED'
  | 'UNKNOWN';

type StepExecutionTracking = {
  hasError: boolean;
  hasWarning: boolean;
  isTrackable: boolean;
  jobName: string;
  stepName: string;
  status: StepExecutionStatus;
  duration: number;
  processedItems: number;
  totalItems: number;
};

type JobStatus = 'COMPLETED' | 'STARTING' | 'STARTED' | 'STOPPING' | 'STOPPED' | 'FAILED' | 'ABANDONED' | 'UNKNOWN';

type JobExecutionTracking = {
  error: boolean;
  warning: boolean;
  status: JobStatus;
  currentStep: number;
  totalSteps: number;
  steps: StepExecutionTracking[];
};

type JobInstance = {
  label: string;
  code: string;
  type: string;
};

type JobExecutionArchives = Record<
  string,
  {
    label: string;
    files: Record<string, string>;
  }
>;

type JobExecution = {
  jobInstance: JobInstance;
  tracking: JobExecutionTracking;
  isStoppable: boolean;
  meta: {
    id: string;
    logExists: boolean;
    archives: JobExecutionArchives;
  };
};

type DownloadLink = {
  label: string;
  archiver: string;
  key: string;
};

const getDownloadLinks = (jobExecutionArchives: JobExecutionArchives | null): DownloadLink[] => {
  if (!jobExecutionArchives) {
    return [];
  }

  let downloadLinks: DownloadLink[] = [];
  Object.keys(jobExecutionArchives).forEach(archiver => {
    const archive = jobExecutionArchives[archiver];
    const fileNames = Object.keys(archive.files);

    if (fileNames.length === 1) {
      downloadLinks.push({
        label: archive.label,
        archiver: archiver,
        key: fileNames[0],
      });

      return;
    }

    fileNames.forEach(fileName => {
      downloadLinks.push({
        label: fileName,
        archiver: archiver,
        key: fileName,
      });
    });
  });

  return downloadLinks;
};

export {getDownloadLinks};
export type {JobExecution, JobExecutionArchives, JobExecutionTracking, JobInstance, StepExecutionTracking};
