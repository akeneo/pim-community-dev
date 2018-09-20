import {create, denormalize} from 'akeneoenrichedentity/domain/model/record/data/file';
import File, {NormalizedFile, denormalizeFile} from 'akeneoenrichedentity/domain/model/file';

describe('akeneo > enriched entity > domain > model > record > data --- file', () => {
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
    }).toThrow('FileData expect a File as parameter to be created');
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
});
