import {StepExecution, Failure} from '../models';

type JobExecution = {
  stepExecutions?: StepExecution[];
  failures: Failure[];
};

export type {JobExecution};
