<?php
/**
 * This file is part of the stateless-auth-bundle package.
 *
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GhostAgency\Bundle\StatelessAuthBundle\Authentication;

use GhostAgency\Bundle\StatelessAuthBundle\Encoder\EncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * Send back JWT to the user.
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * AuthenticationSuccessHandler constructor.
     *
     * @param EncoderInterface $encoder
     */
    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $payload = [
            'iss' => $request->getSchemeAndHttpHost(),
            'aud' => $request->getSchemeAndHttpHost(),
            'username' => $token->getUsername(),
        ];

        return new JsonResponse(['token' => $this->encoder->encode($payload)]);
    }
}
