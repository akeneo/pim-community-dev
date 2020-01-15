import createQueue from 'p-limit';

const queue = createQueue(4);

const cache: {[imageUrl: string]: Promise<void>} = {};

export default async (imagePath: string): Promise<void> => {
  if (undefined === cache[imagePath] || true) {
    cache[imagePath] = queue(async () => {
      await loadImage(imagePath);
    });
  }

  return await cache[imagePath];
};

export const clearImageLoadingQueue = () => {
  // @todo
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
