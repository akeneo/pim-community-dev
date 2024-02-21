import {
  canShowJobExecutionDetail,
  jobCanBeStopped,
  JobExecutionRow,
  getJobExecutionRowTrackingProgressLabel,
} from './JobExecutionTable';
import {StepExecutionRowTracking} from './StepExecutionRowTracking';

const stepTracking: StepExecutionRowTracking = {
  has_error: false,
  warning_count: 0,
  is_trackable: true,
  processed_items: 2,
  total_items: 10,
  status: 'IN_PROGRESS',
  duration: 42,
};

const jobExecutionRow: JobExecutionRow = {
  job_execution_id: 1,
  started_at: '2020-01-01T00:00:00+00:00',
  tracking: {
    total_step: 2,
    current_step: 1,
    steps: [stepTracking],
  },
  has_error: true,
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

const fakeTranslate = (key: string): string => key;

test('it can get the label for a given job execution row tracking', () => {
  expect(getJobExecutionRowTrackingProgressLabel(fakeTranslate, jobExecutionRow)).toEqual(
    'akeneo_job_process_tracker.tracking.in_progress'
  );

  expect(getJobExecutionRowTrackingProgressLabel(fakeTranslate, {...jobExecutionRow, status: 'FAILED'})).toEqual(
    'akeneo_job_process_tracker.tracking.untrackable'
  );

  expect(
    getJobExecutionRowTrackingProgressLabel(fakeTranslate, {
      ...jobExecutionRow,
      tracking: {...jobExecutionRow.tracking, steps: [{...stepTracking, total_items: 0}]},
    })
  ).toEqual('akeneo_job_process_tracker.tracking.estimating');

  expect(
    getJobExecutionRowTrackingProgressLabel(fakeTranslate, {
      ...jobExecutionRow,
      tracking: {...jobExecutionRow.tracking, steps: [{...stepTracking, status: 'COMPLETED'}]},
    })
  ).toEqual('akeneo_job_process_tracker.tracking.completed');

  expect(
    getJobExecutionRowTrackingProgressLabel(fakeTranslate, {
      ...jobExecutionRow,
      tracking: {...jobExecutionRow.tracking, steps: [{...stepTracking, status: 'STARTING'}]},
    })
  ).toEqual('akeneo_job_process_tracker.tracking.not_started');
});
