import { FileInfo } from '../../components';
declare const useFakeMediaStorage: (defaultPath?: string | null) => (string | ((file: File, onProgress: (ratio: number) => void) => Promise<FileInfo>) | null)[];
export { useFakeMediaStorage };
