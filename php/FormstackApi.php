<?php
/**
 * Copyright (c) 2014 Formstack, LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright 2014 Formstack, LLC
 * @license   http://www.opensource.org/licenses/mit-license.php
 * @link      http://www.formstack.com/developers
 */

class FormstackApi {
    private $apiUrl = 'https://www.formstack.com/api/v2/';
    private $accessToken = '';
    public  $finalized = true;

    public function __construct($accessToken) {
        $this->accessToken = $accessToken;
    }

    public function getForms($folderOrganized = false) {
        $arguments = array(
            'folders'   =>  $folderOrganized ? 1 : 0,
        );

        $responseJson = $this->request('form.json', 'GET', $arguments);
        $response = json_decode($responseJson);

        if ($folderOrganized) {
            // Folders are returned as properties of the response->forms object
            // Converting response->forms to an array to be similar in behavior
            // to when there are no folders
            $response->forms = (array) $response->forms;
        }

        return $response->forms;
    }

    public function request($endpoint, $verb = 'GET', $arguments = array()) {
        if (empty($endpoint)) {
            throw new Exception('You must include an enpoint to request');
        }

        $validVerbs = array(
            'GET',
            'PUT',
            'POST',
            'DELETE',
        );

        // Ensure HTTP verb is declared properly
        $verb = strtoupper($verb);
        $url = $this->apiUrl . $endpoint;

        if (!in_array($verb, $validVerbs)) {
            throw new Exception('Your requests must be performed with one of the '
                . 'following verbs: ' . implode(', ', $validVerbs) . '.'
            );
        }

        $ch = curl_init();

        if (!empty($arguments)) {
            if ($verb === 'GET') {
                $url .= '?' . http_build_query($arguments);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arguments));
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->accessToken
            )
        );

        if ($verb === 'POST' || $verb === 'PUT') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if ($verb === 'PUT' || $verb === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
        }

        $result = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorCode = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);

        curl_close($ch);

        if ($httpStatus < 200 || $httpStatus >= 300) {
            if ($this->finalized) {
                throw new Exception('Request failed. Exception code contains HTTP Status.', $httpStatus);
            }

            print 'Server Response: ' . print_r($result, true) . "\n\n"
                . 'HTTP Status Code: ' . $httpStatus . "\n\n" . 'Curl Error Code: '
                . print_r($curlErrorCode, true) . "\n\n" . 'Curl Error Message: '
                . print_r($curlErrorMessage, true);
        }

        return $result;
    }
}
