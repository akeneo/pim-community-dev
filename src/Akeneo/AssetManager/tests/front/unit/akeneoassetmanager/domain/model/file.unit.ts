import {createFileFromNormalized, areFilesEqual, isFileInStorage} from 'akeneoassetmanager/domain/model/file';

describe('akeneo > asset family > domain > model --- file', () => {
  test('I can get the filepath or original filename for an uploaded file', () => {
    expect(
      createFileFromNormalized({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
      }).filePath
    ).toBe('file/path.png');
    expect(
      createFileFromNormalized({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
      }).originalFilename
    ).toBe('my_filename.png');
  });

  test('I can test if the file is stored', () => {
    expect(
      isFileInStorage(
        createFileFromNormalized({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
        })
      )
    ).toBe(true);
    expect(
      isFileInStorage(
        createFileFromNormalized({
          originalFilename: 'my_filename.png',
          filePath: '/tmp/file/path.png',
        })
      )
    ).toBe(false);
  });

  test('I can get the file information for an stored file', () => {
    expect(
      createFileFromNormalized({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).filePath
    ).toBe('file/path.png');
    expect(
      createFileFromNormalized({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).originalFilename
    ).toBe('my_filename.png');
    expect(
      createFileFromNormalized({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).size
    ).toBe(10);
    expect(
      createFileFromNormalized({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).mimeType
    ).toBe('image/png');
    expect(
      createFileFromNormalized({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).extension
    ).toBe('png');
  });

  test('I can denormalize a file', () => {
    expect(createFileFromNormalized(null)).toBe(null);
    expect(
      createFileFromNormalized({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
      })
    ).toEqual({
      originalFilename: 'my_filename.png',
      filePath: 'file/path.png',
    });
    expect(
      createFileFromNormalized({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      })
    ).toEqual({
      originalFilename: 'my_filename.png',
      filePath: 'file/path.png',
      size: 10,
      mimeType: 'image/png',
      extension: 'png',
    });
  });

  test('I can test if a file is equal to another', () => {
    expect(
      areFilesEqual(
        createFileFromNormalized({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
          size: 10,
          mimeType: 'image/png',
          extension: 'png',
        }),
        createFileFromNormalized({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
          size: 10,
          mimeType: 'image/png',
          extension: 'png',
        })
      )
    ).toBe(true);
    expect(
      areFilesEqual(
        createFileFromNormalized({
          originalFilename: 'another_filename.png',
          filePath: 'file/path.png',
          size: 10,
          mimeType: 'image/png',
          extension: 'png',
        }),
        createFileFromNormalized({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
          size: 10,
          mimeType: 'image/jpg',
          extension: 'png',
        })
      )
    ).toBe(false);
    expect(
      areFilesEqual(
        createFileFromNormalized({
          originalFilename: 'another_filename.png',
          filePath: 'file/path.png',
          size: 10,
          mimeType: 'image/png',
          extension: 'png',
        }),
        12
      )
    ).toBe(false);
  });
});
