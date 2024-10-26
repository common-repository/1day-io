<?php

function odp_api_connect($api_url, $method = 'GET', $data = [])
{
    $cache_key = hash('sha256', $api_url . $method . json_encode($data));

    $result = wp_cache_get($cache_key);
    if (false === $result) {
        $method = mb_strtoupper($method);
        $api_url = $method == 'GET' ? $api_url . '?' . http_build_query($data) : $api_url;
        $body = $method == 'GET' ? null : json_encode($data);

        $response = wp_remote_request($api_url, [
            'headers' => odp_get_header_for_api_request(),
            'method' => $method,
            'body' => $body,
            'timeout' => 10
        ]);

        if(is_wp_error($response)) {
            $result = [
                'state' => false,
                'content' => $response->get_error_message()
            ];
            error_log($response->get_error_message());
        } else {
            $result = [
                'state' => (!empty($response['response']['code']) && $response['response']['code'] == 200),
                'content' => json_decode($response['body'])
            ];
        }

        wp_cache_set($cache_key, $result);
    }

    return $result;
}
