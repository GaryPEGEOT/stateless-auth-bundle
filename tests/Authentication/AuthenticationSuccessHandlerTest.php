<?php
/**
 * This file is part of the stateless-auth-bundle package.
 *
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GhostAgency\Bundle\StatelessAuthBundle\Tests\Authentication;

use GhostAgency\Bundle\StatelessAuthBundle\Authentication\AuthenticationSuccessHandler;
use GhostAgency\Bundle\StatelessAuthBundle\Encoder\EncoderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AuthenticationSuccessHandlerTest
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
class AuthenticationSuccessHandlerTest extends TestCase
{
    public function testOnAuthenticationSuccess()
    {
        /** @var EncoderInterface|\PHPUnit_Framework_MockObject_MockObject $encoder */
        $encoder = $this->createMock(EncoderInterface::class);
        $encoder->expects($this->once())
            ->method('encode')
            ->with(['username' => 'bob', 'iss' => 'example.org', 'aud' => 'example.org'])
            ->willReturn('a_JWT_token')
        ;

        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        $request->expects($this->any())->method('getSchemeAndHttpHost')->willReturn('example.org');

        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUsername')->willReturn('bob');

        $handler = new AuthenticationSuccessHandler($encoder);

        $res = $handler->onAuthenticationSuccess($request, $token);

        $this->assertEquals('{"token":"a_JWT_token"}', $res->getContent());
    }
}
