/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    'ko',
    'jquery',
    'uiComponent'
], function (ko, $, Component) {
    'use strict';

    return Component.extend({
        selectValue: ko.observable(0),
        isSelected: ko.observable(false),
        initialize: function (config) {
            this._super();
            this.attributeData = config.attributeData;
        },
        /**
         * get selected option value
         * @return void
         */
        selectAttribute: function () {
            if(this.selectValue()!=0 && this.selectValue()!=''){
                this.isSelected(true);
            } else {
                this.isSelected(false);
            }
        },
        /**
         * get attribute data
         * @param  int
         * @return string
         */
        getArrayData: function (key) {
            return this.attributeData[this.selectValue()][key];
        },
        /**
         * get Is required label
         * @return string
         */
        getIsRequiredLabel: function () {
            if(parseInt(this.attributeData[this.selectValue()]['is_required'])) {
                return "Yes";
            } else {
                return "No";
            }
        },
        /**
         * get all options of the selected attribute
         * @return array
         */
        getOptions: function () {
            return this.attributeData[this.selectValue()]['options'];
        }
    });
});
