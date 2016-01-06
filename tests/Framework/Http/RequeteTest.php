<?php

namespace Tests\Framework\Http;

use Framework\Http\Request;

/**
 * Created by PhpStorm.
 * User: Erjon
 * Date: 05/01/2016
 * Time: 10:35
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider provideInvalidHttpMethod
     */
    public function testUnsupportedHttpMethod($method)
    {
        new Request($method, '/', 'HTTP', '1.1');
    }

    public function provideInvalidHttpMethod()
    {
        return [
            ['FOO'],
            ['BAR'],
            ['BAZ'],
            ['ORI'],
            ['LOL']
        ];
    }

    /**
     * @dataProvider provideRequestParameters
     */
    public function testCreateRequestInstance($method)
    {
        $request = new Request($method, '/', Request::HTTP, '1.1');

        $this->assertSame($method, $request->getMethod());
        $this->assertSame('/', $request->getPath());
        $this->assertSame(Request::HTTP, $request->getScheme());
        $this->assertSame('1.1', $request->getSchemeVersion());
        $this->assertEmpty($request->getHeaders());
        $this->assertEmpty($request->getBody());
    }

    public function provideRequestParameters()
    {
        return [
            [Request::GET],
            [Request::POST],
            [Request::PUT],
            [Request::PATCH],
            [Request::OPTIONS],
            [Request::TRACE],
            [Request::HEAD],
            [Request::DELETE],
        ];
    }
}
