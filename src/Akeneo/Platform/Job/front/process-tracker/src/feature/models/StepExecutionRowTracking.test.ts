import {JobExecutionRow} from './JobExecutionTable';
import {
  getStepExecutionRowTrackingLevel,
  getStepExecutionRowTrackingPercent,
  getStepExecutionRowTrackingProgressLabel,
  StepExecutionRowTracking,
} from './StepExecutionRowTracking';

const stepTracking: StepExecutionRowTracking = {
  error_count: 0,
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
  error_count: 2,
  type: 'export',
  username: 'admin',
  warning_count: 4,
  job_name: 'An export',
  status: 'STARTED',
  is_stoppable: true,
};

const fakeTranslate = (key: string): string => key;

test('it can get the badge level for a given step tracking', () => {
  expect(getStepExecutionRowTrackingLevel(stepTracking)).toEqual('primary');
  expect(getStepExecutionRowTrackingLevel({...stepTracking, error_count: 3})).toEqual('danger');
  expect(getStepExecutionRowTrackingLevel({...stepTracking, warning_count: 2})).toEqual('warning');
});

test('it can get the progress percent for a given step tracking', () => {
  expect(getStepExecutionRowTrackingPercent(stepTracking)).toEqual(20);

  expect(getStepExecutionRowTrackingPercent({...stepTracking, status: 'STARTING'})).toEqual(0);
  expect(getStepExecutionRowTrackingPercent({...stepTracking, status: 'COMPLETED'})).toEqual(100);

  expect(getStepExecutionRowTrackingPercent({...stepTracking, total_items: 0, status: 'STOPPED'})).toEqual(100);

  expect(getStepExecutionRowTrackingPercent({...stepTracking, is_trackable: false})).toEqual('indeterminate');
});

test('it can get the label for a given job status and step tracking', () => {
  expect(getStepExecutionRowTrackingProgressLabel(fakeTranslate, jobExecutionRow, stepTracking)).toEqual(
    'akeneo_job_process_tracker.tracking.in_progress'
  );

  expect(
    getStepExecutionRowTrackingProgressLabel(fakeTranslate, {...jobExecutionRow, status: 'FAILED'}, stepTracking)
  ).toEqual('akeneo_job_process_tracker.tracking.untrackable');

  expect(
    getStepExecutionRowTrackingProgressLabel(fakeTranslate, jobExecutionRow, {...stepTracking, total_items: 0})
  ).toEqual('akeneo_job_process_tracker.tracking.estimating');

  expect(
    getStepExecutionRowTrackingProgressLabel(fakeTranslate, jobExecutionRow, {...stepTracking, status: 'COMPLETED'})
  ).toEqual('akeneo_job_process_tracker.tracking.completed');

  expect(
    getStepExecutionRowTrackingProgressLabel(fakeTranslate, jobExecutionRow, {...stepTracking, status: 'STARTING'})
  ).toEqual('akeneo_job_process_tracker.tracking.not_started');
});
