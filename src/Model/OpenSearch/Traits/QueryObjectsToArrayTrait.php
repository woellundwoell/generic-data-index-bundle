<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch\Traits;

use Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch\Query\AsSubQueryInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch\Query\QueryInterface;

/**
 * @deprecated Will be removed in 2.0, please use
 * Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Traits\QueryObjectsToArrayTrait instead
 */
trait QueryObjectsToArrayTrait
{
    private function convertQueryObjectsToArray(array $params): array
    {
        array_walk_recursive(
            $params,
            static function (&$value) {
                if ($value instanceof AsSubQueryInterface) {
                    $value = $value->toArrayAsSubQuery();
                } elseif ($value instanceof QueryInterface) {
                    $value = $value->toArray(true);
                }
            }
        );

        return $params;
    }
}
