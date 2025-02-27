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

namespace Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\DataObject\FieldDefinitionAdapter;

use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\DefaultSearch\AttributeType;

/**
 * @internal
 */
final class LinkAdapter extends AbstractAdapter
{
    public function getIndexMapping(): array
    {
        return [
            'properties' => [
                'text' => [
                    'type' => AttributeType::TEXT->value,
                ],
                'internalType' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                'internal' => [
                    'type' => AttributeType::LONG->value,
                ],
                'direct' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                'linktype' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                'target' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                'parameters' => [
                    'type' => AttributeType::TEXT->value,
                ],
                'anchor' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                'title' => [
                    'type' => AttributeType::TEXT->value,
                ],
                'accesskey' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                'rel' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                'tabindex' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                'class' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                'attributes' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                '_fieldname' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
                '_language' => [
                    'type' => AttributeType::KEYWORD->value,
                ],
            ],
        ];
    }
}
