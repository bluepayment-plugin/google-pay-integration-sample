<?php

/**
 * @author Blue Media S.A.
 * @copyright Blue Media S.A.
 * @version 2.0.0
 */

/*
 * Konfiguracja parametrów Blue Media.
 * Jeżeli nie posiadasz jeszcze konta w Płatnościach online Blue Media wejdź na stronę https://platnosci.bm.pl
 * i postępuj zgodnie z instrukcjami.
 *
 * Uwaga! Poniższa konfiguracja dotyczy środowiska testowego, nie produkcyjnego!
 */

// Link do bramki, na który wysyłane są parametry do rozpoczęcia transakcji.
define('BM_GATEWAY_URL', 'https://pay-accept.bm.pl');

// Identyfikator serwisu.
// Nadany przez Blue Media po założeniu konta w Płatnościach online.
define('BM_SERVICE_ID', 000000);

// Host serwisu.
define('BM_SERVICE_HOST_NAME', 'www.example.com');

// Separator wartości parametrów używanych przy obliczaniu sumy kontrolnej.
// Dostępny po zalogowaniu się do panelu admina w szczegółach serwisu w sekcji Konfiguracja Hasha.
define('BM_SERVICE_HASH_SEPARATOR', '|');

// Klucz szyfrujący używany przy obliczaniu sumy kontrolnej.
// Dostępny po zalogowaniu się do panelu admina w szczegółach serwisu w sekcji Konfiguracja Hasha.
define('BM_SERVICE_HASH_KEY', 'd41d8cd98f00b204e9800998ecf8427e');

// Algorytm sumy kontrolnej.
// Dostępny po zalogowaniu się do panelu admina w szczegółach serwisu w sekcji Konfiguracja Hasha.
define('BM_SERVICE_HASH_ALGO', 'sha256');

/*
 * Konfiguracja Google Pay.
 */

// Środowisko Google Pay API: TEST lub PRODUCTION.
define('GP_API_ENVIRONMENT', 'TEST');