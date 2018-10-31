/**
 * @author Blue Media S.A.
 * @copyright Blue Media S.A.
 * @version 1.0.0
 */

/**
 * @constructor
 * @param {string} environment
 * @param {string} merchantId
 * @param {string} gatewayId
 * @param {string} gatewayMerchantId
 * @param {array} supportedCardNetworks
 * @param {array} supportedCardAuthMethods
 */
function GooglePay(
    environment,
    merchantId,
    gatewayId,
    gatewayMerchantId,
    supportedCardNetworks,
    supportedCardAuthMethods
) {
    this.environment = environment;
    this.merchantId = merchantId;
    this.gatewayId = gatewayId;
    this.gatewayMerchantId = gatewayMerchantId;
    this.supportedCardAuthMethods = supportedCardAuthMethods;
    this.supportedCardNetworks = supportedCardNetworks;

    this.client = null;
    this.transactionAmount = 0;
    this.transactionCurrency = '';
    this.transactionStatus = '';

    this.payButtonContainerId = 'js-pay-button-wrapper';
}

/**
 * Tworzy i dodaje do drzewa DOM przycisk Zapłać przez GPay.
 *
 * @private
 * @param {function} onPaidCallback
 * @see {@link https://developers.google.com/pay/api/web/reference/client#createButton}
 */
GooglePay.prototype._createPayButton = function (onPaidCallback) {
    const self = this,
        button = self.client.createButton({
            onClick: function () {
                self._onPayButtonClickCallback(onPaidCallback)
            }
        });

    document.getElementById(self.payButtonContainerId).appendChild(button);
};

/**
 * Zwraca dane do zapytania o płatność.
 *
 * @private
 * @returns {object} {@link https://developers.google.com/pay/api/web/reference/object#PaymentDataRequest}
 */
GooglePay.prototype._getRequestData = function () {
    return {
        allowedPaymentMethods: this.supportedCardAuthMethods,
        cardRequirements: {
            allowedCardNetworks: this.supportedCardNetworks
        },
        merchantId: this.merchantId,
        paymentMethodTokenizationParameters: {
            tokenizationType: 'PAYMENT_GATEWAY',
            parameters: {
                'gateway': this.gatewayId,
                'gatewayMerchantId': this.gatewayMerchantId
            }
        },
        transactionInfo: this._getRequestTransactionData()
    }
};

/**
 * Zwraca dane transakcji do zapytania o płatność.
 *
 * @private
 * @returns {object} {@link https://developers.google.com/pay/api/web/reference/object#PaymentDataRequest}
 */
GooglePay.prototype._getRequestTransactionData = function () {
    return {
        currencyCode: this.transactionCurrency,
        totalPrice: this.transactionAmount,
        totalPriceStatus: this.transactionStatus,
    };
};

/**
 * Inicjalizuje klienta Google Pay API.
 *
 * @private
 * @see {@link https://developers.google.com/pay/api/web/reference/client}
 */
GooglePay.prototype._initApiClient = function () {
    this.client = new google.payments.api.PaymentsClient({
        environment: this.environment
    });
};

/**
 * Wykonuje się po kliknięciu na przycisk Zapłać przez GPay.
 *
 * @private
 * @param {function} onPaidCallback
 */
GooglePay.prototype._onPayButtonClickCallback = function (onPaidCallback) {
    this.client.loadPaymentData(this._getRequestData())
        .then(function (paymentData) {
            onPaidCallback(paymentData);
        })
        .catch(function (errorMessage) {
            this.onErrorCallback(errorMessage);
        });
};

/**
 * Pobiera dane dotyczące płatności w celu zwiększenia wydajności.
 *
 * @private
 * @see {@link https://developers.google.com/pay/api/web/reference/client#prefetchPaymentData}
 */
GooglePay.prototype._prefetchTransactionData = function () {
    this.client.prefetchPaymentData({
        transactionInfo: this._getRequestTransactionData()
    });
};

/**
 * Inicjalizuje Google Pay.
 *
 * @param {function} onPaidCallback
 * @see {@link https://developers.google.com/pay/api/web/reference/client#isReadyToPay}
 */
GooglePay.prototype.init = function (onPaidCallback) {
    const self = this;

    self._initApiClient();

    self.client.isReadyToPay({allowedPaymentMethods: self.supportedCardAuthMethods})
        .then(function (response) {
            if (response.result) {
                self._createPayButton(onPaidCallback);
                self._prefetchTransactionData();
            } else {
                self.onErrorCallback(response);
            }
        })
        .catch(function (errorMessage) {
            self.onErrorCallback(errorMessage);
        });
};

/**
 * Wyświetlenie komunikatu w przypadku wystąpienia błędu.
 *
 * @param {string} errorMessage
 */
GooglePay.prototype.onErrorCallback = function (errorMessage) {
    console.error(errorMessage);
};

/**
 * Ustawia kwotę transakcji.
 *
 * @param {numeric} amount
 */
GooglePay.prototype.setTransactionAmount = function (amount) {
    this.transactionAmount = amount;
};

/**
 * Ustawia walutę transakcji.
 *
 * @param {string} currency
 */
GooglePay.prototype.setTransactionCurrency = function (currency) {
    this.transactionCurrency = currency;
};

/**
 * Ustawia status transakcji.
 *
 * @param {string} status
 * @see {@link https://developers.google.com/pay/api/web/reference/object#TransactionInfo}
 */
GooglePay.prototype.setTransactionStatus = function (status) {
    this.transactionStatus = status;
};