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

namespace Pimcore\Bundle\GenericDataIndexBundle\Model\Search\DataObject\SearchResult;

use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\ElementType;
use Pimcore\Bundle\GenericDataIndexBundle\Model\Search\Interfaces\ElementSearchResultItemInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Permission\DataObjectPermissions;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Search\SearchResultItem\LazyLoading\DataObjectLazyLoadingHandlerInterface;

class DataObjectSearchResultItem implements ElementSearchResultItemInterface
{
    private int $id;

    private int $parentId;

    private string $type;

    private string $key;

    private int $index;

    private bool $published;

    private string $path;

    private string $fullPath;

    private int $userOwner;

    private ?int $userModification;

    private ?string $locked;

    private bool $isLocked;

    private ?int $creationDate;

    private ?int $modificationDate;

    private string $className;

    private ?string $classDefinitionIcon;

    private bool $workflowWithPermissions;

    private bool $hasChildren;

    private array $searchIndexData;

    private DataObjectPermissions $permissions;

    private DataObjectLazyLoadingHandlerInterface $lazyLoadingHandler;

    public function getElementType(): ElementType
    {
        return ElementType::DATA_OBJECT;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): DataObjectSearchResultItem
    {
        $this->id = $id;

        return $this;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): DataObjectSearchResultItem
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): DataObjectSearchResultItem
    {
        $this->type = $type;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): DataObjectSearchResultItem
    {
        $this->key = $key;

        return $this;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): DataObjectSearchResultItem
    {
        $this->index = $index;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): DataObjectSearchResultItem
    {
        $this->published = $published;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): DataObjectSearchResultItem
    {
        $this->path = $path;

        return $this;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    public function setFullPath(string $fullPath): DataObjectSearchResultItem
    {
        $this->fullPath = $fullPath;

        return $this;
    }

    public function getUserOwner(): int
    {
        return $this->userOwner;
    }

    public function setUserOwner(int $userOwner): DataObjectSearchResultItem
    {
        $this->userOwner = $userOwner;

        return $this;
    }

    public function getUserModification(): ?int
    {
        return $this->userModification;
    }

    public function setUserModification(?int $userModification): DataObjectSearchResultItem
    {
        $this->userModification = $userModification;

        return $this;
    }

    public function getLocked(): ?string
    {
        return $this->locked;
    }

    public function setLocked(?string $locked): DataObjectSearchResultItem
    {
        $this->locked = $locked;

        return $this;
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(?bool $isLocked): DataObjectSearchResultItem
    {
        $this->isLocked = (bool)$isLocked;

        return $this;
    }

    public function getCreationDate(): ?int
    {
        return $this->creationDate;
    }

    public function setCreationDate(?int $creationDate): DataObjectSearchResultItem
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getModificationDate(): ?int
    {
        return $this->modificationDate;
    }

    public function setModificationDate(?int $modificationDate): DataObjectSearchResultItem
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): DataObjectSearchResultItem
    {
        $this->className = $className;

        return $this;
    }

    public function getClassDefinitionIcon(): ?string
    {
        return $this->classDefinitionIcon;
    }

    public function setClassDefinitionIcon(?string $classDefinitionIcon): DataObjectSearchResultItem
    {
        $this->classDefinitionIcon = $classDefinitionIcon;

        return $this;
    }

    public function isHasWorkflowWithPermissions(): bool
    {
        if (!isset($this->workflowWithPermissions)) {
            $this->lazyLoad();
        }

        return $this->workflowWithPermissions;
    }

    public function setHasWorkflowWithPermissions(?bool $workflowWithPermissions): DataObjectSearchResultItem
    {
        $this->workflowWithPermissions = (bool)$workflowWithPermissions;

        return $this;
    }

    public function isHasChildren(): bool
    {
        if (!isset($this->hasChildren)) {
            $this->lazyLoad();
        }

        return $this->hasChildren;
    }

    public function setHasChildren(bool $hasChildren): DataObjectSearchResultItem
    {
        $this->hasChildren = $hasChildren;

        return $this;
    }

    public function getSearchIndexData(): array
    {
        if (!isset($this->searchIndexData)) {
            $this->lazyLoad();
        }

        return $this->searchIndexData;
    }

    public function setSearchIndexData(array $searchIndexData): DataObjectSearchResultItem
    {
        $this->searchIndexData = $searchIndexData;

        return $this;
    }

    public function getPermissions(): DataObjectPermissions
    {
        return $this->permissions;
    }

    public function setPermissions(DataObjectPermissions $permissions): DataObjectSearchResultItem
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function withLazyLoadingHandler(
        ?DataObjectLazyLoadingHandlerInterface $lazyLoadingHandler
    ): DataObjectSearchResultItem {
        $clone = clone $this;
        $clone->lazyLoadingHandler = $lazyLoadingHandler;

        return $clone;
    }

    private function lazyLoad(): void
    {
        if (isset($this->lazyLoadingHandler)) {
            $this->lazyLoadingHandler->lazyLoad($this);
        }
    }
}
