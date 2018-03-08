<?php
/**
 * This file is part of the stateless-auth-bundle package.
 *
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace GhostAgency\Bundle\StatelessAuthBundle\Encoder;

/**
 * JWT encoder & decoder
 *
 * @see https://jwt.io/
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
interface EncoderInterface
{
    /**
     * Turn a payload into a JWT.
     *
     * @param array $payload
     *
     * @return string
     */
    public function encode(array $payload): string;

    /**
     * Turn a JWT into a payload (and check validity).
     *
     * @param string $token
     *
     * @return object
     */
    public function decode(string $token);
}
