<?php
/**
 * Copyright (c) 2010 Formstack, LLC
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
 * @copyright 2010 Formstack, LLC
 * @license   http://www.opensource.org/licenses/mit-license.php
 * @link      http://www.Formstack.com/api.html
 */

class Formstack {

    private static $api_url = 'https://www.formstack.com/api';

    private $api_key = null;

    public function __construct($api_key) {

        $this->api_key = $api_key;
    }

    /**
     * Returns a list of forms in an account. The response includes all of the
     * information returned by the form method, with the exception of
     * information about each form field and the HTML or JavaScript required to
     * display the form.
     *
     * @link http://support.formstack.com/index.php?pg=kb.page&id=23
     * @return array
     */
    public function forms() {

        return self::request($this->api_key, 'forms', array());
    }

    /**
     * Returns detailed information about a form.
     *
     * @link http://support.formstack.com/index.php?pg=kb.page&id=24
     * @param  mixed $id form id
     * @return array
     */
    public function form($id) {

        return self::request($this->api_key, 'form', array('id' => $id));
    }

    /**
     * Returns data collected for a form.
     *
     * @link http://support.formstack.com/index.php?pg=kb.page&id=25
     * @param mixed $id form id
     * @param array $args optional arguments
     * @return array
     */
    public function data($id, $args = array()) {

        $args['id'] = $id;
        return self::request($this->api_key, 'data', $args);
    }

    /**
     * Returns a single submission collected for a form.
     *
     * @link http://support.formstack.com/index.php?pg=kb.page&id=26
     * @param mixed $id form id
     * @param array $args optional arguments
     * @return array
     */
    public function submission($id, $args = array()) {

        $args['id'] = $id;
        return self::request($this->api_key, 'submission', $args);
    }

    /**
     * This method submits data to a form. This method does not honor any
     * validation or default values configured for a field. because of the lack
     * of validation checks, it is not intended for day-to-day use for public
     * submissions. The form must be configured to store submissions in the
     * database. An error will be returned if the maximum number of submissions
     * for the account has been reached.
     *
     * @link http://support.formstack.com/index.php?pg=kb.page&id=27
     * @param mixed $id form id
     * @param array $args optional arguments
     * @return array
     */
    public function submit($id, $args = array()) {

        $args['id'] = $id;
        return self::request($this->api_key, 'submit', $args);
    }

    /**
     * This method makes changes to an existing submission. Only values
     * supplied within the arguments will be overwritten.
     *
     * @link http://support.formstack.com/index.php?pg=kb.page&id=28
     * @param mixed $id submission id
     * @param array $args
     * @return array
     */
    public function edit($id, $args = array()) {

        $args['id'] = $id;
        return self::request($this->api_key, 'submit', $args);
    }

    /**
     * Deletes an existing submission.
     *
     * @link http://support.formstack.com/index.php?pg=kb.page&id=29
     * @param mixed $id submission id
     * @param array $args optional arguments
     * @return array
     */
    public function delete($id, $args = array()) {

        $args['id'] = $id;
        return self::request($this->api_key, 'delete', $args);
    }

    /**
     * Makes a Formstack API call and returns the response as an array.
     *
     * @param string $api_key Formstack API key
     * @param string $method API method
     * @param array $args API arguments
     * @return array
     */
    public static function request($api_key, $method, $args = array()) {

        $args['api_key'] = $api_key;
        $args['type'] = 'php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$api_url . '/' . $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $res = curl_exec($ch);
        curl_close($ch);

        if(empty($res)) die("Unexpected Error");

        $php_res = unserialize($res);

        if ($php_res['status'] == 'error')
            die('API Error: '.$php_res['error']);

        return $php_res['response'];
    }
}

?>