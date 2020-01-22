<?php
/**
 * @author Blue Media S.A.
 * @copyright Blue Media S.A.
 * @version 1.0.2
 */

/*
 * Konfiguracja parametrów Blue Media.
 * Jeżeli nie posiadasz jeszcze konta w Płatnościach online Blue Media wejdź na stronę https://platnosci.bm.pl
 * i postępuj zgodnie z instrukcjami.
 *
 * Uwaga! Poniższa konfiguracja dotyczy środowiska testowego, nie produkcyjnego!
 */

// Link do bramki, na który wysyłane są parametry do rozpoczęcia transakcji.
$bmGatewayUrl = 'https://pay-accept.bm.pl/payment';

// Identyfikator akceptanta.
// Nadany przez Blue Media po założeniu konta w Płatnościach online.
$bmAcceptorId = 000000;

// Identyfikator serwisu.
// Nadany przez Blue Media po założeniu konta w Płatnościach online.
$bmServiceId = 000000;

// Separator wartości parametrów używanych przy obliczaniu sumy kontrolnej.
// Dostępny po zalogowaniu się do panelu admina w szczegółach serwisu w sekcji Konfiguracja Hasha.
$bmServiceHashSeparator = '|';

// Klucz szyfrujący używany przy obliczaniu sumy kontrolnej.
// Dostępny po zalogowaniu się do panelu admina w szczegółach serwisu w sekcji Konfiguracja Hasha.
$bmServiceHashKey = 'd41d8cd98f00b204e9800998ecf8427e';

$bmTransactionAmount = '1.00';

/*
 * Konfiguracja Google Pay.
 */

// Środowisko Google Pay API: TEST lub PRODUCTION.
$googlePayApiEnvironment = 'TEST';

// Identyfikator bramki obsługującej obciążenie karty nadany Blue Media przez Google.
// Uwaga! Wartość jest STAŁA, nie powinna być zmieniana!
$googlePayGateway = 'bluemedia';

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Przykład integracji z Google Pay</title>
</head>
<body>
<?php
if ('POST' === $_SERVER['REQUEST_METHOD']) {
    /*
     * Start transakcji metodą przedtransakcji.
     * Szczegółowe informacje na temat startowania transakcji metodą przedtransakcji znajdują się w dodatku do specyfikacji integracji.
     */
    $data = [
        'ServiceID'     => $bmServiceId,                          // Parametr wymagany.
        'OrderID'       => date('YmdHis'),                  // Parametr wymagany.
        'Amount'        => $bmTransactionAmount,                  // Parametr wymagany.
        'Description'   => 'Google Pay test',                     // Parametr wymagany.
        'GatewayID'     => '1512',
        'Currency'      => 'PLN',
        'CustomerEmail' => 'test@example.com',
        'CustomerIP'    => '127.0.0.1',
        'Title'         => 'Google Pay test',
        // Kodujemy dane uzyskane od Google Base64.
        'PaymentToken'  => base64_encode($_POST['paymentToken']), // Parametr wymagany.
    ];

    // Obliczamy hash.
    $data['Hash'] = hash(
        'sha256',
        implode($bmServiceHashSeparator, array_values($data)) . $bmServiceHashSeparator . $bmServiceHashKey
    );

    $handle = curl_init();

    curl_setopt_array(
        $handle,
        [
            CURLOPT_HTTPHEADER     => [
                'BmHeader: pay-bm-continue-transaction-url',
            ],
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $bmGatewayUrl,
        ]
    );

    $response = curl_exec($handle);

    echo htmlentities($response);
} else {
    ?>
    <form id="form" method="post">
        <input id="js-payment-token" name="paymentToken" type="hidden" value="">
    </form>
    <div id="js-pay-button-wrapper"></div>
    <script src="js/google-pay.js"></script>
    <script src="https://pay.google.com/gp/p/js/pay.js"></script>
    <script>
        const gp = new GooglePay(
            '<?php echo $googlePayApiEnvironment; ?>',
            // Identyfikator sprzedawcy nadany przez Google.
            // Poniższy identyfikator jest identyfikatorem testowym, produkcyjny identyfikator zostanie nadany przez Google po przejściu weryfikacji.
            '01234567890123456789',
            '<?php echo $googlePayGateway; ?>',
            '<?php echo $bmAcceptorId; ?>',
            // Wspierani dostawcy kartowi. Wartości: AMEX, DISCOVER, JCB nie są wspierane przez Blue Media.
            [/*'AMEX', 'DISCOVER', 'JCB', */'MASTERCARD', 'VISA'],
            ['CARD', 'TOKENIZED_CARD'],
        );
        // Odkomentuj poniższą linię, jeżeli chcesz, aby adres użytkownika był również zwracany z Google Pay API.
        //gp.setRequireShippingAddress(true);
        gp.setTransactionAmount(<?php echo $bmTransactionAmount; ?>);
        gp.setTransactionCurrency('PLN');
        gp.setTransactionStatus('FINAL');
        gp.init(function (data) {
            // Jeżeli ustawiłeś, aby adres użytkownika był również zwracany z Google Pay API będzie on dostępny jako obiekt pod kluczem shippingAddress.
            //console.log(data.shippingAddress);

            document.getElementById('js-payment-token').value = JSON.stringify(data.paymentMethodToken);
            document.getElementById('form').submit();
        });
    </script>
    <?php
}
?>
</body>
</html>