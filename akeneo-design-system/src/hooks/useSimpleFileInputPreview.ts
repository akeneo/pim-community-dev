import {useEffect, useState} from 'react';

const useSimpleFileInputPreview = (defaultValue: File | null = null) => {
  const [file, setFile] = useState<File | null>(defaultValue);
  const [previewUrl, setPreviewUrl] = useState<string | null>();
  useEffect(() => {
    if (file === null) {
      setPreviewUrl(null);
      return;
    }

    setPreviewUrl(URL.createObjectURL(file));
  }, [file, setPreviewUrl]);

  return [file, setFile, previewUrl] as const;
};

export {useSimpleFileInputPreview};
