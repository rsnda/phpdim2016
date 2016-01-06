<?php

namespace Framework\Http;

class Request extends AbstractMessage
{
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const OPTIONS = 'OPTIONS';
    const CONNECT = 'CONNECT';
    const TRACE = 'TRACE';
    const HEAD = 'HEAD';
    const DELETE = 'DELETE';


    private $method;
    private $path;

    /**
     * Constructor.
     *
     * @param string $method        The HTTP verb
     * @param string $path          The resource path on the server
     * @param string $scheme        The protocole name (HTTP or HTTPS)
     * @param string $schemeVersion The scheme version (ie: 1.0, 1.1 or 2.0)
     * @param array  $headers       An associative array of headers
     * @param string $body          The request content
     */
    public function __construct($method, $path, $scheme, $schemeVersion, array $headers = [], $body = '')
    {
        parent:: __construct($scheme, $schemeVersion, $headers, $body);

        $this->setMethod($method);
        $this->path = $path;

    }

    private function setMethod($method)
    {
        $methods = [ 
            self::GET,
            self::POST,
            self::PUT,
            self::PATCH,
            self::OPTIONS,
            self::CONNECT,
            self::TRACE,
            self::HEAD,
            self::DELETE,
        ];

        if (!in_array($method, $methods)) {
            throw new \InvalidArgumentException(sprintf(
                'Method %s is not supported and must be one of %s.',
                $method,
                implode(', ', $methods)
            ));
        }

        $this->method = $method;
    }

    public static function createFromMessage($message)
    {
        if (!is_string($message) || empty($message)) {
            throw new MalformedHttpMessageException($message, 'HTTP message is not valid.');
        }

        // 1. Parse prologue (first required line)
        $lines = explode(PHP_EOL, $message);
        $result = preg_match('#^(?P<method>[A-Z]{3,7}) (?P<path>.+) (?P<scheme>HTTPS?)\/(?P<version>[1-2]\.[0-2])$#', $lines[0], $matches);
        if (!$result) {
            throw new MalformedHttpMessageException($message, 'HTTP message prologue is malformed.');
        }

        array_shift($lines);

        // 2. Parse list of headers (if any)
        $i = 0;
        $headers = [];
        while ($line = $lines[$i]) {
            $result = preg_match('#^([a-z][a-z0-9-]+)\: (.+)$#i', $line, $header);
            if (!$result) {
                throw new MalformedHttpHeaderException(sprintf('Invalid header line at position %u: %s', $i+2, $line));
            }
            list(, $name, $value) = $header;

            $headers[$name] = $value;
            $i++;
        }

        // 3. Parse content (if any)
        $i++;
        $body = '';
        if (isset($lines[$i])) {
            $body = $lines[$i];
        }

        // 4. Construct new instance of Request class with atomic data
        return new self($matches['method'], $matches['path'], $matches['scheme'], $matches['version'], $headers, $body);
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getPath()
    {
        return $this->path;
    }

    protected function createPrologue()
    {
        return sprintf('%s %s %s/%s', $this->method, $this->path, $this->scheme, $this->schemeVersion);
    }
}
