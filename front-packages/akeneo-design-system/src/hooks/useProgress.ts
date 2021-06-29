import {useState} from 'react';
import {arrayUnique} from '../shared';

const useProgress = (steps: string[]) => {
  if (0 === steps.length) {
    throw new Error('Steps array cannot be empty');
  }
  if (arrayUnique(steps).length !== steps.length) {
    throw new Error('Steps array cannot have duplicated names');
  }
  const [current, setCurrent] = useState<number>(0);
  const isCurrent = (step: string) => steps.indexOf(step) === current;
  const next = () => setCurrent(current => (current === steps.length - 1 ? current : current + 1));
  const previous = () => setCurrent(current => (current === 0 ? current : current - 1));

  return [isCurrent, next, previous] as const;
};

export {useProgress};
