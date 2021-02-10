import {arrayUnique} from './array';

type MyObject = {
  code: string;

  description: string;
};

test('It does nothing on empty array', () => {
  expect(arrayUnique([])).toEqual([]);
});

test('It does nothing on unique array', () => {
  const arrayWithoutDuplication = [1, 2, 3, 4, 5];
  expect(arrayUnique(arrayWithoutDuplication)).toEqual(arrayWithoutDuplication);

  const anotherArrayWithoutDuplication = ['a', 'b', 'c', 'd', 'e'];
  expect(arrayUnique(anotherArrayWithoutDuplication)).toEqual(anotherArrayWithoutDuplication);
});

test('It remove duplicate items', () => {
  const arrayWithDuplication = [1, 2, 5, 3, 3, 4, 5];
  const arrayWithoutDuplication = [1, 2, 5, 3, 4];
  expect(arrayUnique(arrayWithDuplication)).toEqual(arrayWithoutDuplication);

  const anotherArrayWithDuplication = ['a', 'a', 'b', 'c', 'e', 'd', 'b', 'e'];
  const anotherArrayWithoutDuplication = ['a', 'b', 'c', 'e', 'd'];
  expect(arrayUnique(anotherArrayWithDuplication)).toEqual(anotherArrayWithoutDuplication);
});

test('It does nothing on unique array', () => {
  const arrayWithoutDuplication = [
    {'code': 'object1', 'description': 'first object'},
    {'code': 'object2', 'description': 'second object'},
    {'code': 'object3', 'description': 'third object'},
  ];

  const comparator = (first: MyObject, second: MyObject) => first.code === second.code;

  expect(arrayUnique<MyObject>(arrayWithoutDuplication, comparator)).toEqual(arrayWithoutDuplication);
});

test('It remove duplicate items on object with comparator', () => {
  const arrayWithDuplication = [
    {'code': 'object1', 'description': 'first object'},
    {'code': 'object2', 'description': 'second object'},
    {'code': 'object2', 'description': 'second object'},
    {'code': 'object3', 'description': 'third object'},
    {'code': 'object1', 'description': 'another object'},
  ];

  const arrayWithoutDuplication = [
    {'code': 'object1', 'description': 'first object'},
    {'code': 'object2', 'description': 'second object'},
    {'code': 'object3', 'description': 'third object'},
  ];

  const comparator = (first: MyObject, second: MyObject) => first.code === second.code;

  expect(arrayUnique<MyObject>(arrayWithDuplication, comparator)).toEqual(arrayWithoutDuplication);
});
