<?php

/**
 * @author Blue Media S.A.
 * @copyright Blue Media S.A.
 * @version 2.0.0
 */

/**
 * Oblicza sumę kontrolną.
 *
 * @param string $algo
 * @param array $params
 * @param string $separator
 * @param string $key
 * @return string
 */
function calculateHash($algo, array $params, $separator, $key)
{
    return hash($algo, implode($separator, array_values($params)) . $separator . $key);
}

/**
 * Losuje kwotę transakcji (między 1,00 zł, a 100,99 zł).
 *
 * @return string
 */
function drawAmount()
{
    return rand(1, 100) .
        '.' .
        str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
}

/**
 * Wysyła zapytanie.
 *
 * @param string $url
 * @param array|string $data
 * @param array $headers
 * @return bool|string
 */
function sendRequest($url, $data, array $headers)
{
    $handle = curl_init();

    curl_setopt_array(
        $handle,
        [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
        ]
    );

    $responseData = curl_exec($handle);

    curl_close($handle);

    return $responseData;
}