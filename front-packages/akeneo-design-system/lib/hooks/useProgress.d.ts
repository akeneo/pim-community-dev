declare const useProgress: (steps: string[]) => readonly [(step: string) => boolean, () => void, () => void];
export { useProgress };
