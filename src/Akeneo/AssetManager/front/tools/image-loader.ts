import createQueue from 'p-limit';

const CONCURRENCY = 4;
const queue = createQueue(CONCURRENCY);

export default async (imagePath: string): Promise<void> => {
  return queue(
    () =>
      new Promise<void>((resolve: any) => {
        const downloadingImage = new Image();
        downloadingImage.onload = () => {
          resolve();
        };
        downloadingImage.src = imagePath;
      })
  );
};
