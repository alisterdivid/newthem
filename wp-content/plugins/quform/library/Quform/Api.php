<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Api
{
    const API_URL = 'https://api.quform.com/wp-json/quform/v1';

    /**
     * Send a request to the Quform API
     *
     * @param   string      $endpoint  The API endpoint to send the request to
     * @param   array       $data      The request data
     * @param   string      $method    The HTTP method to use
     * @return  array|bool             The response array or false on failure
     */
    public function request($endpoint, $data, $method = 'GET')
    {
        $url = self::API_URL . '/' . trim($endpoint, '/');

        $request = wp_remote_request($url, array(
            'method' => $method,
            'body' => $data,
            'timeout' => 10
        ));

        if (is_wp_error($request) || ! strlen($response = wp_remote_retrieve_body($request))) {
            return false;
        }

        $response = json_decode($response, true);

        if (is_array($response)) {
            return $response;
        } else {
            return false;
        }
    }

    /**
     * Send a GET request to the Quform API
     *
     * @param   string      $endpoint  The API endpoint to send the request to
     * @param   array       $data      The request data
     * @return  array|bool             The response array or false on failure
     */
    public function get($endpoint, $data)
    {
        return $this->request($endpoint, $data, 'GET');
    }

    /**
     * Send a POST request to the Quform API
     *
     * @param   string      $endpoint  The API endpoint to send the request to
     * @param   array       $data      The request data
     * @return  array|bool             The response array or false on failure
     */
    public function post($endpoint, $data)
    {
        return $this->request($endpoint, $data, 'POST');
    }
}
