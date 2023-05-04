import {
  getStepExecutionRowTrackingLevel,
  getStepExecutionRowTrackingPercent,
  StepExecutionRowTracking,
} from './StepExecutionRowTracking';

const stepTracking: StepExecutionRowTracking = {
  has_error: false,
  warning_count: 0,
  is_trackable: true,
  processed_items: 2,
  total_items: 10,
  status: 'IN_PROGRESS',
  duration: 42,
};

test('it can get the badge level for a given step tracking', () => {
  expect(getStepExecutionRowTrackingLevel(stepTracking)).toEqual('primary');
  expect(getStepExecutionRowTrackingLevel({...stepTracking, has_error: true})).toEqual('danger');
  expect(getStepExecutionRowTrackingLevel({...stepTracking, warning_count: 2})).toEqual('warning');
  expect(getStepExecutionRowTrackingLevel({...stepTracking, has_error: true, warning_count: 2})).toEqual('danger');
  expect(getStepExecutionRowTrackingLevel({...stepTracking, status: 'PAUSED'})).toEqual('tertiary');
});

test('it can get the progress percent for a given step tracking', () => {
  expect(getStepExecutionRowTrackingPercent(stepTracking)).toEqual(20);

  expect(getStepExecutionRowTrackingPercent({...stepTracking, status: 'STARTING'})).toEqual(0);
  expect(getStepExecutionRowTrackingPercent({...stepTracking, status: 'COMPLETED'})).toEqual(100);

  expect(getStepExecutionRowTrackingPercent({...stepTracking, total_items: 0, status: 'STOPPED'})).toEqual(100);

  expect(getStepExecutionRowTrackingPercent({...stepTracking, is_trackable: false})).toEqual('indeterminate');
});
