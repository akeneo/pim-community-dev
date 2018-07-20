export default async (imagePath: string): Promise<void> => {
  return new Promise((resolve: any) => {
    const downloadingImage = new Image();
    downloadingImage.onload = () => {
      resolve();
    }
    downloadingImage.src = imagePath;
  });
}
