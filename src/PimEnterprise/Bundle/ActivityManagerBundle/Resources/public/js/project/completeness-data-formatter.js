'use strict';

/**
 * Project completeness data formatter.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define([], function () {
        return {
            /**
             * Format a number to a percentage.
             *
             * @param {Collection} completeness
             *
             * @returns {Collection}
             */
            formatToPercentage: function (completeness) {
                var rawTodo = parseInt(completeness.todo);
                var rawInProgress = parseInt(completeness.in_progress);
                var rawDone = parseInt(completeness.done);
                var todo = 0;
                var inProgress = 0;
                var done = 0;
                var total = rawTodo + rawInProgress + rawDone;

                if (0 !== total) {
                    todo = Math.round(rawTodo * 100 / total);
                    inProgress = Math.round(rawInProgress * 100 / total);
                    done = Math.round(rawDone * 100 / total);
                }

                return {
                    todo: todo,
                    in_progress: inProgress,
                    done: done
                };
            }
        };
    }
);
