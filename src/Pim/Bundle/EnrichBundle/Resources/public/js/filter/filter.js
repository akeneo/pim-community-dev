'use strict';

define(['pim/form'], function (BaseForm) {
    return BaseForm.extend({
        removable: false,
        setField: function (field) {
            var data = this.getFormData();
            data.field = field;
            this.setData(data, {silent: true});
        },
        getField: function () {
            return this.getFormData().field;
        },
        setOperator: function (operator) {
            var data = this.getFormData();
            data.operator = operator;
            this.setData(data, {silent: true});
        },
        getOperator: function () {
            return this.getFormData().operator;
        },
        setValue: function (value) {
            var data = this.getFormData();
            data.value = value;
            this.setData(data, {silent: true});
        },
        getValue: function () {
            return this.getFormData().value;
        },
        setRemovable: function (removable) {
            this.removable = removable;
        },
        isRemovable: function () {
            return this.removable;
        },
        removeFilter: function () {
            this.trigger('filter:remove', this.getField());
        }
    });
});
