import File, {createFile, createEmptyFile, denormalizeFile} from 'akeneoenrichedentity/domain/model/file';

describe('akeneo > enriched entity > domain > model --- file', () => {
  test('I can create a new file', () => {
    expect(createFile('file/path.png', 'my_filename.png').normalize()).toEqual({
      originalFilename: 'my_filename.png',
      filePath: 'file/path.png',
    });
    expect(createFile().normalize()).toBe(null);
    expect(createEmptyFile().normalize()).toBe(null);
  });

  test('I cannot create a new file in an invalid state', () => {
    expect(() => {
      createFile(12);
    }).toThrow('File expect a non empty string as filePath to be created');
    expect(() => {
      createFile('my/path.png');
    }).toThrow('File expect a non empty string as originalFilename to be created');
  });

  test('I can get the filepath or original filename', () => {
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

  test('I cannot get the filepath or original filename if the file is empty', () => {
    expect(() => {
      createFile().getFilePath();
    }).toThrow('You cannot get the file path on an empty file');
    expect(() => {
      createFile().getOriginalFilename();
    }).toThrow('You cannot get the original filename on an empty file');
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
  });

  test('I can test if a file is equal to another', () => {
    expect(
      denormalizeFile({
        originalFilename: 'my_filename.png',
        filePath: 'file/path.png',
      }).equals(
        denormalizeFile({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
        })
      )
    ).toBe(true);
    expect(
      denormalizeFile({
        originalFilename: 'another_filename.png',
        filePath: 'file/path.png',
      }).equals(
        denormalizeFile({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
        })
      )
    ).toBe(false);
    expect(
      denormalizeFile({
        originalFilename: 'another_filename.png',
        filePath: 'file/path.png',
      }).equals(12)
    ).toBe(false);
  });
});
