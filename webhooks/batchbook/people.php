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
 */

require_once 'Formstack.php';

define('API_KEY', '');              // Formstack API key
define('BATCHBOOK_ACCOUNT', '');    // BatchBook account
define('BATCHBOOK_TOKEN', '');      // BatchBook API token

$formstack = new Formstack(API_KEY);
$form = $formstack->form($_POST['FormID']);

$person = array();

foreach($form['fields'] as $field) {

    if(empty($_POST[$field['id']])) continue;

    $value = $_POST[$field['id']];

    if($field['type'] == 'name' && !isset($person['first_name'])) {
        $values = fs_extract($value);
        $person['first_name'] = $values['first'];
        $person['last_name']  = $values['last'];
    }

    else if($field['type'] == 'textarea' && !isset($person['notes'])) {

        $person['notes'] = $value;
    }

    $label = strtolower($field['label']);
    foreach(array('title', 'company') as $key) {
        if($label != $key) continue;
        $person[$key] = $value;
    }
}

api_call(BATCHBOOK_ACCOUNT, BATCHBOOK_TOKEN, array('person' => $person));

/**
 * Extracts subvalues from Formstack fields.
 * @param <type> $value
 * @return <type>
 */
function fs_extract($value) {

    if(strpos($value, "\n") === false) return $value;

    $fs = array();
    foreach(explode("\n", $value) as $sub_value) {
        list($sub_key, $sub_value) = explode("=", $sub_value);
        $fs[trim($sub_key)] = trim($sub_value);
    }

    return $fs;
}

/**
 * HTTP Wrapper for BatchBook's people.xml service
 * @link http://developer.batchblue.com/people.html#create
 * @param <type> $account
 * @param <type> $token
 * @param <type> $args
 * @return <type>
 */
function api_call($account, $token, $args) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://{$account}.batchbook.com/service/people.xml");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_USERPWD,"{$token}:x");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}