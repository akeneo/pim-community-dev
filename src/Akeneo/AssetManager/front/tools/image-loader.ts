import PQueue from 'p-queue';

const queue = new PQueue({concurrency: 4});
let abortController: null|AbortController = null;

const cache: {[imageUrl: string]: Promise<void>} = {};
const addToQueue = async (imagePath: string): Promise<void> => {
  queue.start();
  if (undefined === cache[imagePath]) {
    cache[imagePath] = queue.add(async () => {
      try {
        return await loadImage(imagePath);
      } catch (error) {
        delete cache[imagePath];
        throw error;
      }
    });
  }

  return await cache[imagePath];
};

export const clearImageLoadingQueue = () => {
  queue.pause();
  abortController?.abort();
  abortController = null;
};

const loadImage = async (imagePath: string) => {
  if (null === abortController) {
    abortController = new AbortController();
  }

  const response = await fetch(imagePath, {
    signal: abortController.signal
  });

  if (response.ok) {
    return;
  } else {
    throw new Error('Cannot load image ' + imagePath);
  }
};

export default addToQueue;
