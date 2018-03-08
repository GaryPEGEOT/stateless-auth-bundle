<?php
/**
 * This file is part of the stateless-auth-bundle package.
 *
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GhostAgency\Bundle\StatelessAuthBundle\Tests\Encoder;

use GhostAgency\Bundle\StatelessAuthBundle\Encoder\TokenEncoder;
use PHPUnit\Framework\TestCase;

/**
 * Class TokenEncoderTest
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
class TokenEncoderTest extends TestCase
{
    /**
     * @var TokenEncoder
     */
    private $encoder;

    /**
     * Test JWT encoding.
     */
    public function testEncode()
    {
        $now = new \DateTimeImmutable('1901-01-01 00:00:00');
        $payload = [
            'name' => 'John DOE',
            'iat'  => $now->getTimestamp(),
            'exp'  => $now->modify('+1 year'),
        ];
        $token = explode('.', $this->encoder->encode($payload));

        $this->assertCount(3, $token);
        $this->assertEquals('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9', $token[0], 'Header should match');
        $this->assertEquals(
            'eyJpYXQiOi0yMTc3NDUyODAwLCJleHAiOnsiZGF0ZSI6IjE5MDItMDEtMDEgMDA6MDA6MDAuMDAwMDAwIiwidGltZXpvbmVfdHlwZSI6MywidGltZXpvbmUiOiJVVEMifSwibmFtZSI6IkpvaG4gRE9FIn0',
            $token[1],
            'Payload should match'
        );
        $this->assertEquals('Mb-tukUtQawXHzptShKDJHxX_6q0S5dmtIFXCV_3-WI', $token[2], 'Signature should match');
    }

    /**
     * Test JWT decoding.
     */
    public function testDecode()
    {
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.XbPfbIHMI6arZ3Y922BhjWgQzWXcXNrz0ogtVhfEd2o";

        $payload = $this->encoder->decode($token);

        $this->assertEquals('John Doe', $payload->name);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->encoder = new TokenEncoder('secret');
    }
}
