import File, {createFile, createEmptyFile, denormalizeFile} from 'akeneoreferenceentity/domain/model/file';

describe('akeneo > reference entity > domain > model --- file', () => {
  test('I can create a new uploaded file', () => {
    expect(createFile('file/path.png', 'my_filename.png').normalize()).toEqual({
      originalFilename: 'my_filename.png',
      filePath: 'file/path.png',
    });
    expect(createFile().normalize()).toBe(null);
    expect(createEmptyFile().normalize()).toBe(null);
  });

  test('I can create a new stored file', () => {
    expect(createFile('file/path.png', 'my_filename.png', 10, 'image/png', 'png').normalize()).toEqual({
      originalFilename: 'my_filename.png',
      filePath: 'file/path.png',
      size: 10,
      mimeType: 'image/png',
      extension: 'png',
    });
    expect(createFile().normalize()).toBe(null);
    expect(createEmptyFile().normalize()).toBe(null);
  });

  test('I cannot create a new file in an invalid state', () => {
    expect(() => {
      createFile(12);
    }).toThrow('File expects a non empty string as filePath to be created');
    expect(() => {
      createFile('my/path.png');
    }).toThrow('File expects a non empty string as originalFilename to be created');
  });

  test('I can get the filepath or original filename for an uploaded file', () => {
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
      }).getFilePath()
    ).toBe('file/path.png');
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
      }).getOriginalFilename()
    ).toBe('my_filename.png');
  });

  test('I can test if the file is stored', () => {
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
      }).isInStorage()
    ).toBe(true);
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: '/tmp/file/path.png',
      }).isInStorage()
    ).toBe(false);
  });

  test('I can get the file information for an stored file', () => {
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).getFilePath()
    ).toBe('file/path.png');
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).getOriginalFilename()
    ).toBe('my_filename.png');
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).getSize()
    ).toBe(10);
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).getMimeType()
    ).toBe('image/png');
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).getExtension()
    ).toBe('png');
  });

  test('I cannot get the file information if the file is empty', () => {
    expect(() => {
      createFile().getFilePath();
    }).toThrow('You cannot get the file path on an empty file');
    expect(() => {
      createFile().getOriginalFilename();
    }).toThrow('You cannot get the original filename on an empty file');
    expect(() => {
      createFile().getSize();
    }).toThrow('You cannot get the size on an uploaded or empty file');
    expect(() => {
      createFile().getMimeType();
    }).toThrow('You cannot get the mime type on an uploaded or empty file');
    expect(() => {
      createFile().getExtension();
    }).toThrow('You cannot get the extension on an uploaded or empty file');
  });

  test('I can denormalize a file', () => {
    expect(denormalizeFile(null).normalize()).toBe(null);
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
      }).normalize()
    ).toEqual({
      originalFilename: 'my_filename.png',
      filePath: 'file/path.png',
    });
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).normalize()
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
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).equals(
        denormalizeFile({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
          size: 10,
          mimeType: 'image/png',
          extension: 'png',
        })
      )
    ).toBe(true);
    expect(
      denormalizeFile({
        originalFilename: 'another_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).equals(
        denormalizeFile({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
          size: 10,
          mimeType: 'image/jpg',
          extension: 'png',
        })
      )
    ).toBe(false);
    expect(
      denormalizeFile({
        originalFilename: 'another_filename.png',
        filePath: 'file/path.png',
        size: 10,
        mimeType: 'image/png',
        extension: 'png',
      }).equals(12)
    ).toBe(false);
  });
});
