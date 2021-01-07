<?php

/**
 * @author Blue Media S.A.
 * @copyright Blue Media S.A.
 * @version 1.0.3
 */

require_once '_common.php';
require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Przykład integracji Google Pay</title>
</head>
<body>
<?php

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    /*
     * Start transakcji metodą przedtransakcji.
     * Szczegółowe informacje na temat startowania transakcji metodą przedtransakcji znajdują się w dodatku do specyfikacji integracji.
     */
    $params = [
        'ServiceID' => BM_SERVICE_ID,
        'OrderID' => date('YmdHis'),
        'Amount' => $_POST['amount'],
        'Description' => 'Google Pay test',
        'GatewayID' => '1512',
        'Currency' => 'PLN', // parametr opcjonalny
        'CustomerEmail' => 'test@example.com', // parametr opcjonalny
        'CustomerIP' => '127.0.0.1', // parametr opcjonalny
        'Title' => 'Google Pay test', // parametr opcjonalny
        'PaymentToken' => base64_encode($_POST['paymentToken']),
    ];
    $params['Hash'] = calculateHash(BM_SERVICE_HASH_ALGO, $params, BM_SERVICE_HASH_SEPARATOR, BM_SERVICE_HASH_KEY);

    $responseData = sendRequest(
        BM_GATEWAY_URL . '/payment',
        $params,
        [
            'BmHeader: pay-bm-continue-transaction-url',
        ]
    );

    echo '<pre>', htmlentities($responseData), '</pre>';

    $response = simplexml_load_string($responseData);
    if (false !== $response && isset($response->redirecturl)) {
        ?>

        <a href="<?php echo $response->redirecturl; ?>" target="_blank">
            KLIKNIJ, aby przejść do płatności
        </a>

        <?php
    }

    exit;
}

$bmTransactionAmount = drawAmount();

$requestData = [
    'ServiceID' => BM_SERVICE_ID,
    'MerchantDomain' => BM_SERVICE_HOST_NAME,
];
$requestData['Hash'] = calculateHash(BM_SERVICE_HASH_ALGO, $requestData, BM_SERVICE_HASH_SEPARATOR, BM_SERVICE_HASH_KEY);

$responseData = sendRequest(
    BM_GATEWAY_URL . '/webapi/googlePayMerchantInfo',
    json_encode($requestData),
    [
        'BmHeader: pay-bm',
        'Content-Type: application/json',
    ]
);
$responseData = json_decode($responseData, true);
if (!isset($responseData['authJwt'])) {
    echo 'Invalid API response, check configuration.';
    exit;
}

?>
<h1>Google Pay test</h1>
<form id="form" method="post">
    <input name="amount" type="hidden" value="<?php echo $bmTransactionAmount; ?>">
    <input id="js-payment-token" name="paymentToken" type="hidden" value="">
</form>
<div id="js-pay-button-wrapper"></div>
<script src="js/google-pay.js?_t=<?php echo time(); ?>"></script>
<script src="https://pay.google.com/gp/p/js/pay.js"></script>
<script>
    const gp = new GooglePay(
        '<?php echo GP_API_ENVIRONMENT; ?>',
        '<?php echo $responseData['authJwt']; ?>',
        '<?php echo $responseData['merchantId']; ?>',
        '<?php echo $responseData['merchantOrigin']; ?>',
        '<?php echo $responseData['merchantName']; ?>',
        '<?php echo $responseData['acceptorId']; ?>',
        // Wspierani dostawcy kartowi. Wartości: AMEX, DISCOVER, JCB nie są wspierane przez Blue Media.
        [/*'AMEX', 'DISCOVER', 'JCB', */'MASTERCARD', 'VISA'],
        ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
    );
    // Odkomentuj poniższą linię, jeżeli chcesz, aby adres użytkownika był również zwracany z Google Pay API.
    // gp.setRequireShippingAddress(true);
    gp.setTransactionAmount('<?php echo $bmTransactionAmount; ?>');
    gp.setTransactionStatus('FINAL');
    gp.setTransactionCurrencyCode('PLN');
    gp.setTransactionCountryCode('PL');
    gp.init(function (data) {
        // Jeżeli ustawiłeś, aby adres użytkownika był również zwracany z Google Pay API będzie on dostępny jako obiekt pod kluczem shippingAddress.
        // console.debug(data.shippingAddress);

        document.getElementById('js-payment-token').value = JSON.stringify(data.paymentMethodData.tokenizationData.token);
        document.getElementById('form').submit();
    });
</script>
</body>
</html>