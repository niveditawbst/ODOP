/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
],
function (Component, rendererList) {
    'use strict';
    rendererList.push(
        {
            type: 'appmerce_worldline_bcmc',
            component: 'Appmerce_Worldline/js/view/payment/method-renderer/worldline-method'
        },
        {
            type: 'appmerce_worldline_ideal',
            component: 'Appmerce_Worldline/js/view/payment/method-renderer/worldline-method'
        },
        {
            type: 'appmerce_worldline_maestro',
            component: 'Appmerce_Worldline/js/view/payment/method-renderer/worldline-method'
        },
        {
            type: 'appmerce_worldline_mastercard',
            component: 'Appmerce_Worldline/js/view/payment/method-renderer/worldline-method'
        },
        {
            type: 'appmerce_worldline_visa',
            component: 'Appmerce_Worldline/js/view/payment/method-renderer/worldline-method'
        },
        {
            type: 'appmerce_worldline_vpay',
            component: 'Appmerce_Worldline/js/view/payment/method-renderer/worldline-method'
        }
    );

    /** Add view logic here if needed */
        return Component.extend({});
    }
);
