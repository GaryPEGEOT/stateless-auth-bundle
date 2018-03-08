<?php
/**
 * This file is part of the stateless-auth-bundle package.
 *
 * (c) Gary PEGEOT <garypegeot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GhostAgency\Bundle\StatelessAuthBundle\DependencyInjection\GhostAgencyStatelessAuthExtension;

use GhostAgency\Bundle\StatelessAuthBundle\DependencyInjection\GhostAgencyStatelessAuthExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class GhostAgencyStatelessAuthExtensionTest
 *
 * @author Gary PEGEOT <garypegeot@gmail.com>
 */
class GhostAgencyStatelessAuthExtensionTest extends TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();
        $ext = new GhostAgencyStatelessAuthExtension();
        $ext->load([['hash_key' => 'The hash key', 'token_ttl' => 1234]], $container);

        $definition = $container->findDefinition('ghost_agency_stateless_auth.token_encoder');

        $this->assertEquals(['The hash key', 1234], $definition->getArguments());
    }
}
