# Przykład integracji Google Pay

Przykład integracji Google Pay z bramką [Blue Media](https://bluemedia.pl) metodą przedtransakcji.

## Konfiguracja

Utwórz plik config.php (kopiując plik config.sample.php), a następnie zaktualizuj go zgodnie z konfiguracją Twojego serwisu.

W przypadku, jeżeli API Google Pay zwróci poniższy błąd, upewnij się, że przycisk "Zapłać przez G Pay" jest osadzony na stronie pod adresem ustawionym w BM_SERVICE_HOST_NAME. Dotyczy to również środowiska lokalnego.
```
{
    "statusCode": "DEVELOPER_ERROR",
    "errorCode": 2,
    "statusMessage": "merchantOrigin mismatch!"
}
```