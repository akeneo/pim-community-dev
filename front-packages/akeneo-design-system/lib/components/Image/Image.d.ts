import React from 'react';
declare type Fit = 'cover' | 'contain';
declare const Image: React.ForwardRefExoticComponent<Omit<React.ImgHTMLAttributes<HTMLImageElement>, "height" | "width" | "alt" | "src" | "fit" | "isStacked"> & {
    src: string | null;
    alt: string;
    width?: number | undefined;
    height?: number | undefined;
    fit?: Fit | undefined;
    isStacked?: boolean | undefined;
} & React.RefAttributes<HTMLImageElement>>;
export { Image };
