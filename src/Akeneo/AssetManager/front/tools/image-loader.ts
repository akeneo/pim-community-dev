import PQueue from 'p-queue';

const queue = new PQueue({concurrency: 4});

const cache: {[imageUrl: string]: Promise<void>} = {};
export default async (imagePath: string): Promise<void> => {
  if (undefined === cache[imagePath]) {
    cache[imagePath] = queue.add(async () => {
      await loadImage(imagePath);
    });
  }

  return await cache[imagePath];
};

export const clearImageLoadingQueue = () => {
  queue.clear();
};

const loadImage = (imagePath: string) => {
  return new Promise<void>((resolve: any) => {
    const downloadingImage = new Image();
    downloadingImage.onload = () => {
      resolve();
    };
    downloadingImage.src = imagePath;
  });
};
