import PQueue from 'p-queue';

export const createQueue = (concurrency: number) => {
  const queue = new PQueue({concurrency});

  return queue.add;
};
