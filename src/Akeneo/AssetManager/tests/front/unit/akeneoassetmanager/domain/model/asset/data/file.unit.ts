import {create, denormalize} from 'akeneoassetmanager/domain/model/asset/data/file';
import File, {createFileFromNormalized} from 'akeneoassetmanager/domain/model/file';

describe('akeneo > asset family > domain > model > asset > data --- file', () => {
  test('I can create a new FileData with a File value', () => {
    expect(
      create(
        createFileFromNormalized({originalFilename: 'starck.png', filePath: '/a/g/d/f/fzefzgezgafzgzg.png'})
      ).normalize()
    ).toEqual({
      originalFilename: 'starck.png',
      filePath: '/a/g/d/f/fzefzgezgafzgzg.png',
    });
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
    expect(denormalize({originalFilename: 'starck.png', filePath: '/a/g/d/f/fzefzgezgafzgzg.png'}).getFile()).toEqual({
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
        createFileFromNormalized({
          originalFilename: 'my_filename.png',
          filePath: 'file/path.png',
        })
      ).equals(
        create(
          createFileFromNormalized({
            originalFilename: 'my_filename.png',
            filePath: 'file/path.png',
          })
        )
      )
    ).toBe(true);
    expect(
      create(
        createFileFromNormalized({
          originalFilename: 'another_filename.png',
          filePath: 'file/path.png',
        })
      ).equals(
        create(
          createFileFromNormalized({
            originalFilename: 'my_filename.png',
            filePath: 'file/path.png',
          })
        )
      )
    ).toBe(false);
    expect(
      create(
        createFileFromNormalized({
          originalFilename: 'another_filename.png',
          filePath: 'file/path.png',
        })
      ).equals(12)
    ).toBe(false);
  });
});
