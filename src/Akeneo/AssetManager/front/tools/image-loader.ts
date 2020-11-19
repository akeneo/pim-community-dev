import PQueue from 'p-queue';

const queue = new PQueue({concurrency: 4});

const cache: {[imageUrl: string]: Promise<void>} = {};
const addToQueue = async (imagePath: string): Promise<void> => {
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
  queue.clear();
};

const loadImage = (imagePath: string) => {
  return new Promise<void>((resolve: any, reject: any) => {
    const downloadingImage = new Image();
    downloadingImage.onload = () => {
      resolve();
    };
    downloadingImage.onerror = () => {
      reject(new Error('Cannot load image ' + imagePath));
    };
    downloadingImage.src = imagePath;
  });
};

export default addToQueue;
