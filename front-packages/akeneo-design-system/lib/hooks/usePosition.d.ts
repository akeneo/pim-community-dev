import { RefObject } from 'react';
declare type VerticalPosition = 'up' | 'down';
declare type HorizontalPosition = 'left' | 'right';
declare const useVerticalPosition: (ref: RefObject<HTMLElement>, forcedPosition?: VerticalPosition | undefined) => VerticalPosition | undefined;
declare const useHorizontalPosition: (ref: RefObject<HTMLElement>, forcedPosition?: HorizontalPosition | undefined) => HorizontalPosition | undefined;
export { useVerticalPosition, useHorizontalPosition };
export type { VerticalPosition, HorizontalPosition };
