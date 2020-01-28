import * as React from 'react';
import loadImage from 'akeneoassetmanager/tools/image-loader';

const useImageLoader = (url: string): string | undefined => {
  const [loadedUrl, setLoadedUrl] = React.useState<string | undefined>(undefined);

  React.useEffect(() => {
    const timeout = setTimeout(() => {
      setLoadedUrl(undefined);
    }, 300);

    loadImage(url).then(() => {
      clearTimeout(timeout);
      setLoadedUrl(url);
    });

    return () => clearTimeout(timeout);
  }, [url]);

  return loadedUrl;
};

export default useImageLoader;
