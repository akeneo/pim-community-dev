export default interface Uploader<Entity> {
  upload: (file: any, onProgress: (ratio: number) => void) => Promise<Entity>;
}
