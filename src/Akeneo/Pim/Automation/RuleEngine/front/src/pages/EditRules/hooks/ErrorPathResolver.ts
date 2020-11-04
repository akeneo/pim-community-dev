import {FormData} from '../edit-rules.types';

/**
 * Before sending the form to the backend, we remove all the NULL conditions and actions, loosing their
 * previous indexes. For example, if the current form value is like
 * [ 0 => null,
 *   1 => valid_action,
 *   2 => invalid_action,
 *   3 => null ],
 * we remove the null actions and send it to the backend like this:
 * [ 0 => valid_action,
 *   1 => invalid_action ].
 * Then, the backend will validate this model and return error paths like this:
 * [ { path: 'actions[1].a_field', message: 'there is an error' } ]
 * As you can see, the 1 in this field is the line number of the submitted field, not the current form line
 * number. This method is here to get the real form number to display the errors in the right lines.
 *
 * In other words, this method will return the nth non-null element, with n = lineNumberFromBackend.
 */
const getRealLineNumber = (formData: any[], lineNumberFromBackend: number) => {
  let notNullElements = 0;
  for (let i = 0; i < formData.length; i++) {
    if (formData[i]) {
      if (lineNumberFromBackend === notNullElements) {
        return i;
      }
      notNullElements++;
    }
  }

  return notNullElements;
};

const getErrorPath = (formData: FormData, path: string) => {
  const matches = /^(actions|conditions)\[(\d+)\](.*)/g.exec(path);
  if (matches) {
    const type: 'actions' | 'conditions' = matches[1] as
      | 'actions'
      | 'conditions';
    const lineNumber = Number(matches[2]);
    const subfield: string = matches[3];

    if (subfield === '') {
      /* The error path is not linked to a specific field (value, field, operator...)
       * As in react-hook-form, every error is linked to a field, we need to link it to a fake field. */
      return `content.${type}[${getRealLineNumber(
        formData.content[type],
        lineNumber
      )}].__fromBackend__`;
    }

    return `content.${type}[${getRealLineNumber(
      formData.content[type],
      lineNumber
    )}]${subfield}`;
  }

  return path;
};

export {getErrorPath};
