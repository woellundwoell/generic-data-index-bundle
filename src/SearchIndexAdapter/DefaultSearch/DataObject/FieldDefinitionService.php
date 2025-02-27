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

namespace Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\DataObject;

use Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DataObject\AdapterInterface;
use Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DataObject\FieldDefinitionServiceInterface;
use Pimcore\Model\DataObject\ClassDefinition;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @internal
 */
final readonly class FieldDefinitionService implements FieldDefinitionServiceInterface
{
    public function __construct(
        private ServiceLocator $adapterLocator
    ) {
    }

    public function getFieldDefinitionAdapter(ClassDefinition\Data $fieldDefinition): ?AdapterInterface
    {
        $adapter = null;

        if ($this->adapterLocator->has($fieldDefinition->getFieldType())) {
            try {
                $adapter = $this->adapterLocator->get($fieldDefinition->getFieldType());
            } catch (ContainerExceptionInterface) {
                return null;
            }
            $adapter->setFieldDefinition($fieldDefinition);
        }

        return $adapter;
    }

    public function normalizeValue(?ClassDefinition\Data $fieldDefinition, mixed $value): mixed
    {
        if ($fieldDefinition === null) {
            return $value;
        }

        if ($adapter = $this->getFieldDefinitionAdapter($fieldDefinition)) {
            return $adapter->normalize($value);
        }

        return null;
    }
}
