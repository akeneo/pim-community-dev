import {canShowJobExecutionDetail, jobCanBeStopped, JobExecutionRow} from './JobExecutionTable';

const jobExecutionRow: JobExecutionRow = {
  job_execution_id: 1,
  started_at: '2020-01-01T00:00:00+00:00',
  tracking: {
    total_step: 2,
    current_step: 1,
  },
  error_count: 2,
  type: 'export',
  username: 'admin',
  warning_count: 4,
  job_name: 'An export',
  status: 'IN_PROGRESS',
  is_stoppable: true,
};

test('it can tell if the job can be stopped', () => {
  expect(jobCanBeStopped(jobExecutionRow)).toBe(true);
  expect(jobCanBeStopped({...jobExecutionRow, status: 'STARTING'})).toBe(true);

  expect(jobCanBeStopped({...jobExecutionRow, is_stoppable: false})).toBe(false);

  expect(jobCanBeStopped({...jobExecutionRow, status: 'STOPPED'})).toBe(false);
  expect(jobCanBeStopped({...jobExecutionRow, status: 'STOPPING'})).toBe(false);
  expect(jobCanBeStopped({...jobExecutionRow, status: 'FAILED'})).toBe(false);
  expect(jobCanBeStopped({...jobExecutionRow, status: 'ABANDONED'})).toBe(false);
  expect(jobCanBeStopped({...jobExecutionRow, status: 'COMPLETED'})).toBe(false);
  expect(jobCanBeStopped({...jobExecutionRow, status: 'UNKNOWN'})).toBe(false);
});

test('it can tell if user can show job execution detail', () => {
  const isGranted = (acl: string) => acl === 'pim_importexport_export_execution_show';

  expect(canShowJobExecutionDetail(isGranted, jobExecutionRow)).toBe(true);
  expect(canShowJobExecutionDetail(isGranted, {...jobExecutionRow, type: 'import'})).toBe(false);
  expect(canShowJobExecutionDetail(isGranted, {...jobExecutionRow, type: 'quick_export'})).toBe(true);
});
