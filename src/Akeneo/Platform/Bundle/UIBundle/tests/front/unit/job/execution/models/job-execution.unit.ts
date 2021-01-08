import {
  getDownloadLinks,
  JobExecutionArchives,
} from '../../../../../../Resources/public/js/job/execution/models/job-execution';

describe('job execution', () => {
  it('should provide a array of download link', () => {
    const jobExecution: JobExecutionArchives = {
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
    };

    const downloadLinks = getDownloadLinks(jobExecution);

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
});
