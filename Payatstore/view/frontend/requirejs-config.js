var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'MMT_Payatstore/js/action/set-shipping-information-mixin': true
            }
        }
    },
    "map": {
        "*": {
            "Magento_Checkout/js/model/shipping-save-processor/default": "MMT_Payatstore/js/shipping-save-processor"
        }
    }
};