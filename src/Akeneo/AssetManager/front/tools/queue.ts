const split = <T>(queue: T[], n: number): T[][] => {
  return [queue.slice(0, n), queue.slice(n)];
};

export const addQueueSupport = <T, U>(method: (...args: T[]) => U, maxBatchSize: number) => {
  type Item = {arguments: T[]; resolve: (result: U) => void};
  let queue: Item[] = [];
  let currentCallStackSize = 0;

  const callNextBatch = <T, U>(method: (...args: T[]) => U, maxBatchSize: number) => {
    let [nextBatchToCall, newQueue] = split<Item>(queue, maxBatchSize - currentCallStackSize);
    queue = newQueue;

    // We call the next batch
    nextBatchToCall.forEach(async itemToCall => {
      currentCallStackSize++;

      method.call(null, ...itemToCall.arguments).then(function() {
        currentCallStackSize--;
        itemToCall.resolve(arguments[0]);

        callNextBatch(method, maxBatchSize);
      });
    });
  };

  return function() {
    const parameters = Object.values(arguments) as T[];
    return new Promise((resolve: (result: U) => void) => {
      queue.push({arguments: parameters, resolve});

      if (currentCallStackSize < maxBatchSize) {
        callNextBatch<T, U>(method, maxBatchSize);
      }
    });
  } as any;
};
