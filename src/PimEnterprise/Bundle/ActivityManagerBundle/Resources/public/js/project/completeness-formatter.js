'use strict';

/**
 * Project completeness formatter from number of products to percentage.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define([], function () {
        return {
            /**
             * Get a "KPI object" representing the given project completeness progression in product percentage.
             * Receives:
             * {
             *      todo: 20,
             *      in_progress: 10,
             *      done: 0
             * }
             *
             * Returns given completeness in percentage:
             * {
             *      todo: 67,
             *      in_progress: 33,
             *      done: 0
             * }
             *
             * @param {Object} completeness
             *
             * @returns {Object}
             */
            getCompletenessProgress: function (completeness) {
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
