<?php
namespace CAC\Component\PhonegapApi;

/**
 * Phonegap API wrapper
 *
 * Connect to the Phonegap API. This is an abstraction of the Phonegap API.
 *
 * @author Nick de Groot <nick@crazyawesomecompany.com>
 */
class PhonegapApi
{
    const ANDROID = 'android';
    const BLACKBERRY ='blackberry';
    const IOS = 'ios';

    /**
     * The Phonegap Username/Emailaddress
     * @var string
     */
    private $username;

    /**
     * The Phonegap password
     * @var string
     */
    private $password;

    /**
     * The Phonegap API url
     * @var string
     */
    private $apiUrl = 'https://build.phonegap.com';

    /**
     * Start a new Phonegap API
     *
     * @param string $username The Phonegap username/emailaddress
     * @param string $password The Phonegap password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function updateApplicationPackage($id, $package)
    {
        $url = sprintf('/api/v1/apps/%d', $id);
        $params = array(
            'file' => sprintf('@%s', $package)
        );

        return $this->put($url, $params);
    }

    public function buildApplication($id, $os = null, $key = null)
    {
        $url = sprintf('/api/v1/apps/%d/build', $id);
        if ($os) {
            // @todo Make possible to do multiple OS
            $url = sprintf('%s/%s', $url, $os);
        }

        if ($key) {

        }

        return $this->post($url);
    }

    /**
     * Unlock an Android key
     *
     * @param integer $appId
     * @param integer $keyId
     * @param string  $keyPassword
     * @param string  $keyStorePassword
     */
    public function unlockAndroidKey($appId, $keyId, $keyPassword, $keyStorePassword)
    {
        $url = sprintf('/api/v1/apps/%d', $appId);
        $data = array(
            'keys' => array(
                'android' => array(
                    'id' => $keyId,
                    'key_pw' => $keyPassword,
                    'keystore_pw' => $keyStorePassword
                )
            )
        );

        $params = array(
            'data' => json_encode($data)
        );

        return $this->put($url, $params);
    }

    /**
     * Get all user/application/key information
     */
    public function getPersonalData()
    {
        $url = '/api/v1/me';

        return $this->get($url);
    }

    /**
     * Get all applications
     */
    public function getApplications()
    {
        $url = '/api/v1/apps';

        return $this->get($url);
    }

    /**
     * Get specific application information
     *
     * @param integer $id
     */
    public function getApplication($id)
    {
        $url = sprintf('/api/v1/apps/%d', $id);

        return $this->get($url);
    }

    public function getApplicationDownloadUrl($id, $os)
    {
        $url = sprintf('/api/v1/apps/%d/%s', $id, $os);

        return $this->get($url);
    }

    public function getKeys()
    {
        $url = '/api/v1/keys';

        return $this->get($url);
    }

    public function getOsKeys($os)
    {
        $url = sprintf('/api/v1/keys/%s', $os);

        return $this->get($url);
    }

    /**
     * Get a specific key
     *
     * @param integer $id The key id
     * @param string  $os The OS identifier
     */
    public function getKey($id, $os)
    {
        $url = sprintf('/api/v1/keys/%s/%d', $os, $id);

        return $this->get($url);
    }

    protected function get($url)
    {
        return $this->request('GET', $url);
    }

    protected function put($url, $parameters = array())
    {
        return $this->request('PUT', $url, $parameters);
    }

    protected function post($url, $parameters = array())
    {
        return $this->request('POST', $url, $parameters);
    }

    protected function request($method, $url, $parameters = array())
    {
        $url = $this->apiUrl . $url;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $this->username, $this->password));

        if ($parameters) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        }

        if(($result = curl_exec($ch)) === false) {
            // Some error occured when doing the curl request
            throw new Exception(
                    sprintf("cURL error: %s", curl_error($ch)),
                    curl_errno($ch)
            );
        }

        $info = curl_getinfo($ch);
        return $result;
    }
}
