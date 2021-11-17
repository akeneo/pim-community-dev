import {Failure} from './Failure';
import {Warning} from './Warning';

type StepExecution = {
  job: string;
  label: string;
  status: string;
  summary: {[key: string]: string};
  startedAt: string;
  endedAt: string;
  warnings: Warning[];
  errors: string[];
  failures: Failure[];
};

export type {StepExecution};
