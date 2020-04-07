import PQueue from 'p-queue';

const queue = new PQueue({concurrency: 4});

const cache: {[imageUrl: string]: Promise<string>} = {};
const addToQueue = async (imagePath: string): Promise<string> => {
  if (undefined === cache[imagePath]) {
    cache[imagePath] = queue.add(async () => {
      try {
        return await loadImage(imagePath);
      } catch (error) {
        delete cache[imagePath];
        throw error;
      }
    });
  }

  return await cache[imagePath];
};

export const clearImageLoadingQueue = () => {
  queue.clear();
};

const loadImage = (imagePath: string): Promise<string> => {
  return new Promise<string>((resolve, reject) => {
    var request = new XMLHttpRequest();
    request.open('GET', imagePath);
    request.responseType = 'blob';

    request.onload = function () {
      if (request.status === 200) {
        var reader = new FileReader();
        reader.readAsDataURL(request.response);
        reader.onloadend = function () {
          if (null === reader.result) {
            reject();
          }
          resolve(reader.result as string);
        };
      } else {
        reject(new Error("Image didn't load successfully; error code:" + request.statusText));
      }
    };

    request.onerror = function () {
      reject(new Error('There was a network error.'));
    };

    request.send();
  });
};

export default addToQueue;
