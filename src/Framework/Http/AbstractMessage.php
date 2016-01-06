<?php
/**
 * Created by PhpStorm.
 * User: Erjon
 * Date: 06/01/2016
 * Time: 11:37
 */

namespace Framework\Http;


abstract class AbstractMessage
{
    const HTTP = 'HTTP';
    const HTTPS = 'HTTPS';

    const VERSION_1_0 = '1.0';
    const VERSION_1_1 = '1.1';
    const VERSION_2_0 = '2.0';

    protected $scheme;
    protected $schemeVersion;
    protected $headers;
    protected $body;

    public function __construct($scheme, $schemeVersion, array $headers = [], $body = '')
    {
        $this->headers = [];
        $this->setScheme($scheme);
        $this->setSchemeVersion($schemeVersion);
        $this->setHeaders($headers);
        $this->body = $body;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getSchemeVersion()
    {
        return $this->schemeVersion;
    }

    protected function setHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->addHeader($header, $value);
        }
    }

    public function getHeader($name)
    {
        $name = strtolower($name);

        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * Adds a new normalized header value to the list of all headers.
     *
     * @param string $header The HTTP header name
     * @param string $value  The HTTP header value
     *
     * @throws \RuntimeException
     */
    private function addHeader($header, $value)
    {
        $header = strtolower($header);

        if (isset($this->headers[$header])) {
            throw new \RuntimeException(sprintf(
                'Header %s is already defined and cannot be set twice.',
                $header
            ));
        }

        $this->headers[$header] = (string) $value;
    }


    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody()
    {
        return $this->body;
    }

    private function setScheme($scheme)
    {
        $schemes = [ self::HTTP, self::HTTPS ];
        if (!in_array($scheme, $schemes)) {
            throw new \InvalidArgumentException(sprintf(
                'Scheme %s is not supported and must be one of %s.',
                $scheme,
                implode(', ', $schemes)
            ));
        }

        $this->scheme = $scheme;
    }

    private function setSchemeVersion($version)
    {
        $versions = [ self::VERSION_1_0, self::VERSION_1_1, self::VERSION_2_0 ];
        if (!in_array($version, $versions)) {
            throw new \InvalidArgumentException(sprintf(
                'Scheme version %s is not supported and must be one of %s.',
                $version,
                implode(', ', $versions)
            ));
        }

        $this->schemeVersion = $version;
    }

    protected abstract function createPrologue();


    /**
     * Returns the Message instance as an HTTP string representation.
     *
     * @return string
     */
    final public function getMessage()
    {
        $message = $this->createPrologue();

        if (count($this->headers)) {
            $message.= PHP_EOL;
            foreach ($this->headers as $header => $value) {
                $message.= sprintf('%s: %s', $header, $value).PHP_EOL;
            }
        }

        $message.= PHP_EOL;
        if ($this->body) {
            $message.= $this->body;
        }

        return $message;
    }

    /**
     * String representation of a Message instance.
     *
     * Alias of getMessage().
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getMessage();
    }

}