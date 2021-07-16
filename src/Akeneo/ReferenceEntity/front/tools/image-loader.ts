import PQueue from 'p-queue';

const queue = new PQueue({concurrency: 4});
const cache: {[imageUrl: string]: Promise<void>} = {};

const loadInQueue = async (imagePath: string): Promise<void> => {
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

const clearImageLoadingQueue = () => {
  queue.clear();
};

const loadImage = (imagePath: string) =>
  new Promise<void>((resolve: () => void, reject: (error: Error) => void) => {
    const downloadingImage = new Image();
    downloadingImage.onload = () => {
      resolve();
    };
    downloadingImage.onerror = () => {
      reject(new Error('Cannot load image ' + imagePath));
    };
    downloadingImage.src = imagePath;
  });

export {loadImage, loadInQueue, clearImageLoadingQueue};
