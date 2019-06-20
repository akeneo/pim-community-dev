import {create, denormalize} from 'akeneoreferenceentity/domain/model/record/data/file';
import File, {NormalizedFile, denormalizeFile} from 'akeneoreferenceentity/domain/model/file';

describe('akeneo > reference entity > domain > model > record > data --- file', () => {
  test('I can create a new FileData with a File value', () => {
    expect(
      create(denormalizeFile({originalFilename: 'starck.png', filePath: '/a/g/d/f/fzefzgezgafzgzg.png'})).normalize()
    ).toEqual({
      originalFilename: 'starck.png',
      filePath: '/a/g/d/f/fzefzgezgafzgzg.png',
    });
  });

  test('I cannot create a new FileData with a value other than a File', () => {
    expect(() => {
      create(12);
    }).toThrow('FileData expects a File as parameter to be created');
  });

  test('I can normalize a FileData', () => {
    expect(denormalize({originalFilename: 'starck.png', filePath: '/a/g/d/f/fzefzgezgafzgzg.png'}).normalize()).toEqual(
      {
        originalFilename: 'starck.png',
        filePath: '/a/g/d/f/fzefzgezgafzgzg.png',
      }
    );
  });

  test('I can get the file of a FileData', () => {
    expect(
      denormalize({originalFilename: 'starck.png', filePath: '/a/g/d/f/fzefzgezgafzgzg.png'})
        .getFile()
        .normalize()
    ).toEqual({
      originalFilename: 'starck.png',
      filePath: '/a/g/d/f/fzefzgezgafzgzg.png',
    });
  });

  test('I can test if a file is empty', () => {
    expect(denormalize({originalFilename: 'starck.png', filePath: '/a/g/d/f/fzefzgezgafzgzg.png'}).isEmpty()).toEqual(
      false
    );
    expect(denormalize(null).isEmpty()).toEqual(true);
  });

  test('I can test if a file is equal to another', () => {
    expect(
      create(
        denormalizeFile({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
        })
      ).equals(
        create(
          denormalizeFile({
            originalFilename: 'my_filename.png',
            filePath: 'file/path.png',
          })
        )
      )
    ).toBe(true);
    expect(
      create(
        denormalizeFile({
          originalFilename: 'another_filename.png',
          filePath: 'file/path.png',
        })
      ).equals(
        create(
          denormalizeFile({
            originalFilename: 'my_filename.png',
            filePath: 'file/path.png',
          })
        )
      )
    ).toBe(false);
    expect(
      create(
        denormalizeFile({
          originalFilename: 'another_filename.png',
          filePath: 'file/path.png',
        })
      ).equals(12)
    ).toBe(false);
  });
});
