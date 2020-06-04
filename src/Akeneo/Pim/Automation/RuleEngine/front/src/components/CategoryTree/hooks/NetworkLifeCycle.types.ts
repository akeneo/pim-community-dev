export type NetworkLifeCycle<T> = {
  status: 'COMPLETE' | 'PENDING' | 'ERROR';
  error?: string;
  data?: T | null;
};
