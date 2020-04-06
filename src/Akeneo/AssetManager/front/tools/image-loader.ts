import PQueue from 'p-queue';

const queue = new PQueue({concurrency: 4});

const cache: {[imageUrl: string]: Promise<string>} = {};
const addToQueue = async (imagePath: string): Promise<string> => {
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

const loadImage = (imagePath: string): Promise<string> => {
  return new Promise<string>((resolve, reject) => {
    const downloadingImage = new Image();
    downloadingImage.onload = (image) => {
      resolve((image.currentTarget as HTMLImageElement).src);
    };
    downloadingImage.onerror = (error) => {
      reject(error);
    };
    downloadingImage.src = imagePath;
  });
};

export default addToQueue;
