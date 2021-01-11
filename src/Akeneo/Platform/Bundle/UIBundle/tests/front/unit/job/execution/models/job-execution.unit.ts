import {
  getDownloadLinks,
  JobExecutionArchives,
  isJobFinished,
  JobExecution,
} from '../../../../../../Resources/public/js/job/execution/models/job-execution';

const jobExecution: JobExecution = {
  failures: [],
  meta: {
    logExists: false,
    archives: {
      output: {
        label: 'pim_enrich.entity.job_execution.module.download.output',
        files: {
          'export_2021-01-05_10-33-34.csv': 'export/24/output/export_2021-01-05_10-33-34.csv',
        },
      },
      archive: {
        label: 'pim_enrich.entity.job_execution.module.download.archive',
        files: {
          'export_2021-01-05_10-33-34.zip': 'export/24/archive/export_2021-01-05_10-33-34.zip',
        },
      },
    },
  },
  isStoppable: true,
  jobInstance: {label: 'Nice job', code: 'nice_job', type: 'yes'},
  tracking: {
    error: false,
    warning: false,
    status: 'COMPLETED',
    currentStep: 1,
    totalSteps: 1,
    steps: [],
  },
};

describe('job execution', () => {
  it('should provide a array of download link', () => {
    const downloadLinks = getDownloadLinks(jobExecution.meta.archives);

    expect(downloadLinks).toEqual([
      {
        archiver: 'output',
        key: 'export_2021-01-05_10-33-34.csv',
        label: 'pim_enrich.entity.job_execution.module.download.output',
      },
      {
        archiver: 'archive',
        key: 'export_2021-01-05_10-33-34.zip',
        label: 'pim_enrich.entity.job_execution.module.download.archive',
      },
    ]);
  });

  it('should return empty array when no job execution archives is given', () => {
    const downloadLinks = getDownloadLinks(null);

    expect(downloadLinks).toEqual([]);
  });

  it('should provide the file name as label when archive contain multiple file', () => {
    const jobExecution: JobExecutionArchives = {
      output: {
        label: 'pim_enrich.entity.job_execution.module.download.output',
        files: {
          'export_product_2021-01-05_10-33-34.csv': 'export/24/output/export_product.csv',
          'export_product_model_2021-01-05_10-33-34.csv': 'export/24/output/export_product_model.csv',
        },
      },
    };

    const downloadLinks = getDownloadLinks(jobExecution);

    expect(downloadLinks).toEqual([
      {
        archiver: 'output',
        key: 'export_product_2021-01-05_10-33-34.csv',
        label: 'export_product_2021-01-05_10-33-34.csv',
      },
      {
        archiver: 'output',
        key: 'export_product_model_2021-01-05_10-33-34.csv',
        label: 'export_product_model_2021-01-05_10-33-34.csv',
      },
    ]);
  });

  it('should tell if a job is finished or not based on its status', () => {
    expect(isJobFinished(jobExecution)).toBe(true);
    expect(isJobFinished({...jobExecution, tracking: {...jobExecution.tracking, status: 'STOPPED'}})).toBe(true);
    expect(isJobFinished({...jobExecution, tracking: {...jobExecution.tracking, status: 'FAILED'}})).toBe(true);
    expect(isJobFinished({...jobExecution, tracking: {...jobExecution.tracking, status: 'ABANDONED'}})).toBe(false);
    expect(isJobFinished({...jobExecution, tracking: {...jobExecution.tracking, status: 'UNKNOWN'}})).toBe(false);
  });
});
