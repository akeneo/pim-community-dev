export default async (imagePath: string): Promise<void> => {
  return new Promise<void>((resolve: any) => {
    const downloadingImage = new Image();
    downloadingImage.onload = () => {
      resolve();
    };
    downloadingImage.src = imagePath;
  });
};
