define([
    'jquery',
    'moment'
], function ($, moment) {
    'use strict';

    return function (validator) {

        validator.addRule(
            'custom-validate-telephone',
            function (value, params) {                
                 var phoneno = /^(09|959|\+)([0-9]{6,15})$/i;

                if((value.match(phoneno))){
                    return true;
                }       
            },
            $.mage.__("Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx).")
        );

        return validator;
    };
});